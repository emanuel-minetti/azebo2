<?php /** @noinspection PhpUnused */

namespace Print\Controller;

use AzeboLib\ApiController;
use AzeboLib\Saldo;
use Carry\Model\WorkingMonthTable;
use DateTime;
use Exception;
use Fpdf\Fpdf;
use Laminas\Config\Factory;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Login\Model\UserTable;
use Service\AuthorizationService;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRule;
use WorkingRule\Model\WorkingRuleTable;
use WorkingTime\Model\WorkingDay;

class PrintController extends ApiController {
    private WorkingMonthTable $monthTable;
    private UserTable $userTable;
    private WorkingRuleTable $ruleTable;
    public function __construct(
        AzeboLog $logger,
        WorkingMonthTable $monthTable,
        UserTable $userTable,
        WorkingRuleTable $ruleTable
    ) {
        $this->monthTable = $monthTable;
        $this->userTable = $userTable;
        $this->ruleTable = $ruleTable;
        parent::__construct($logger);
    }
    public function printAction(): JsonModel|Response{
        $this->prepare();
        $yearParam = $this->params('year');
        $monthParam = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ["POST"])) {

            // gather data
            $config = Factory::fromFile('./../server/config/times.config.php', true);
            $userId = $this->httpRequest->getQuery()->user_id;
            $workingMonth = $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0];
            $filename = $this->getFilename();
            $todayString = (new DateTime())->format('d/m/Y');
            $user = $this->userTable->find($userId);
            $name = $user->getFullName();
            $name = $this->handleUmlaut($name);
            $zeichen = $user->username;
            $zeichen = $this->handleUmlaut($zeichen);
            $monat = $this->getMonthString($month);
            $cappingLimitMinutes = $config->get('cappingLimit');
            $kappungsgrenze = '' . Saldo::createFromHoursAndMinutes(0, $cappingLimitMinutes);
            $rules = $this->ruleTable->getByUserIdAndMonth($userId, $month);

            // finalize month
            if (!$workingMonth->finalized) {
                $workingMonth->finalized = true;
                $this->monthTable->update($workingMonth);
            }

            // write PDF
            $pdf = new FPDF('L', 'pt');
            $pdf->AddFont('Calibri', '', 'calibri.php');
            $pdf->AddFont('Calibri', 'B', 'calibrib.php');
            $pdf->SetLineWidth(1);
            $pdf->SetFont('Calibri','B',8.5);
            $pdf->AddPage();
            $pdf->SetXY(680, 0);
            $pdf->Cell(40,10,"Zeiterfassungsbogen - Stand $todayString");
            $pdf->SetXY(15, 30);
            $pdf->Cell(40, 10, 'Name', 1);
            $pdf->SetFont('Calibri', '');
            $pdf->Cell(150, 10, $name, 1);
            $pdf->SetXY(15, 40);
            $pdf->SetFont('Calibri', 'B');
            $pdf->Cell(40, 10, 'Zeichen', 1);
            $pdf->SetFont('Calibri', '');
            $pdf->Cell(150, 10, $zeichen, 1);
            $pdf->SetXY(15, 50);
            $pdf->SetFont('Calibri', 'B');
            $pdf->Cell(40, 10, 'Monat', 1);
            $pdf->SetFont('Calibri', '');
            $pdf->Cell(150, 10, $monat, 1);
            $pdf->SetXY(15, 60);
            $pdf->SetFont('Calibri', 'B');
            $pdf->Cell(40, 10, 'Kapp.-Gr.', 1);
            $pdf->SetFont('Calibri', '');
            $pdf->Cell(150, 10, $kappungsgrenze, 1);


            if (sizeof($rules) === 1) {
                /** @var WorkingRule $rule */
                $rule = $rules[0];
                $percentage = $rule->percentage;
                $workingTime = $rule->isOfficer
                    ? $config->get('workingMinutesPerWeekOfficer')
                    : $config->get('workingMinutesPerWeek');
                $realWorkingTime = $workingTime * $percentage / 100;
                $arbeitszeit = '' . Saldo::createFromHoursAndMinutes(0, $realWorkingTime);
                $arbeitstage = $rule->hasWeekdays ? sizeof($rule->weekdays) : 5;
                $dailyWorkingTime = $realWorkingTime / $arbeitstage;
                $soll = Saldo::createFromHoursAndMinutes(0, $dailyWorkingTime);
                $status = $rule->isOfficer ? 'Beamte/r' : 'Beschäftigte/r';
                $status = $this->handleUmlaut($status);

                $pdf->SetXY(205, 30);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(65, 10, 'WoAz', 1, 0, 'C');
                $pdf->SetFont('Calibri', '');
                $pdf->Cell(65, 10, $arbeitszeit, 1, 0, 'R');
                $pdf->SetXY(205, 40);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(65, 10, 'AT/Wo.', 1, 0, 'C');
                $pdf->SetFont('Calibri', '');
                $pdf->Cell(65, 10, $arbeitstage, 1, 1, 'R');
                $pdf->SetXY(205, 50);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(65, 10, 'Soll', 1, 0, 'C');
                $pdf->SetFont('Calibri', '');
                $pdf->Cell(65, 10, $soll, 1, 1, 'R');
                $pdf->SetXY(205, 60);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(65, 10, 'Status', 1, 0, 'C');
                $pdf->SetFont('Calibri', '');
                $pdf->Cell(65, 10, $status, 1, 1, 'R');
            } else {
            }

            $pdf->Output('F', 'public/files/' . $filename);

            // send filename
            $result = [
                'file' => $filename,
            ];
            return $this->processResult($result, $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }

    private function getFilename(): string {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $charsLength = strlen($chars);
        $filename = '';
        for ($i=0; $i < 10; $i++) {
            try {
                $filename .= $chars[random_int(0, $charsLength - 1)];
            } catch (Exception $ignored) {}
        }
        $filename .= '.pdf';
        return $filename;
    }

    private function getMonthString(DateTime $month): string {
        $result = '';
        switch ($month->format('n')) {
            case '1':
                $result .= 'Januar';
                break;
            case '2':
                $result .= 'Februar';
                break;
            case '3':
                $result .= 'März';
                break;
            case '4':
                $result .= 'April';
                break;
            case '5':
                $result .= 'Mai';
                break;
            case '6':
                $result .= 'Juni';
                break;
            case '7':
                $result .= 'Juli';
                break;
            case '8':
                $result .= 'August';
                break;
            case '9':
                $result .= 'September';
                break;
            case '10':
                $result .= 'Oktober';
                break;
            case '11':
                $result .= 'November';
                break;
            case '12':
                $result .= 'Dezember';
                break;
        }
        $result .= ' ' . $month->format('Y');
        return $result;
    }

    private function handleUmlaut(string $str): string {
        return iconv('UTF-8', 'windows-1252', $str);
}


}