<?php
/** @noinspection DuplicatedCode */
/** @noinspection  PhpUnused*/

namespace Print\Controller;

use AzeboLib\ApiController;
use AzeboLib\DaysOfMonth;
use AzeboLib\FPDF_Auto;
use AzeboLib\Saldo;
use Carry\Model\CarryTable;
use Carry\Model\WorkingMonthTable;
use DateTime;
use Exception;
use Fpdf\Fpdf;
use Laminas\Config\Factory;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Login\Model\UserTable;
use Service\AuthorizationService;
use Service\HolidayService;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRule;
use WorkingRule\Model\WorkingRuleTable;
use WorkingTime\Model\WorkingDay;
use WorkingTime\Model\WorkingDayPart;
use WorkingTime\Model\WorkingDayTable;

class PrintController extends ApiController {
    private WorkingMonthTable $monthTable;
    private UserTable $userTable;
    private WorkingRuleTable $ruleTable;
    private WorkingDayTable $dayTable;
    private CarryTable $carryTable;
    public function __construct(
        AzeboLog $logger,
        WorkingMonthTable $monthTable,
        UserTable $userTable,
        WorkingRuleTable $ruleTable,
        WorkingDayTable $dayTable,
        CarryTable $carryTable
    ) {
        $this->monthTable = $monthTable;
        $this->userTable = $userTable;
        $this->ruleTable = $ruleTable;
        $this->dayTable = $dayTable;
        $this->carryTable = $carryTable;
        parent::__construct($logger);
    }
    /** @noinspection PhpUnusedInspection */
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
            $kappungsgrenze = Saldo::createFromHoursAndMinutes(0, $cappingLimitMinutes);
            $rules = $this->ruleTable->getByUserIdAndMonth($userId, $month);

            // finalize month
            if (!$workingMonth->finalized) {
                $workingMonth->finalized = true;
                $this->monthTable->update($workingMonth);
            }

            // write PDF
            // browser gate for firefox
            $browsers = ['other', 'firefox'];


            foreach ($browsers as $browser) {
                $pdf = $browser === 'other' ? new Fpdf('L', 'pt')
                    : new FPDF_Auto('L', 'pt');
                $pdf->SetTitle('Arbeitszeitbogen');
                $pdf->SetAutoPageBreak(true, 30);
                $pdf->AddFont('Calibri', '', 'calibri.php');
                $pdf->AddFont('Calibri', 'B', 'calibrib.php');
                $pdf->SetLineWidth(1);
                $pdf->SetFont('Calibri', 'B', 8.5);
                $pdf->AddPage();
                $pdf->SetXY(680, 0);
                $pdf->Cell(40, 10, "Zeiterfassungsbogen - Stand $todayString");
                $pdf->SetXY(15, 30);
                $pdf->Cell(40, 10, 'Name', 1);
                $pdf->SetFont('Calibri');
                $pdf->Cell(150, 10, $name, 1);
                $pdf->SetXY(15, 40);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(40, 10, 'Zeichen', 1);
                $pdf->SetFont('Calibri');
                $pdf->Cell(150, 10, $zeichen, 1);
                $pdf->SetXY(15, 50);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(40, 10, 'Monat', 1);
                $pdf->SetFont('Calibri');
                $pdf->Cell(150, 10, $monat, 1);
                $pdf->SetXY(15, 60);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(40, 10, 'Kapp.-Gr.', 1);
                $pdf->SetFont('Calibri');
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
                    $pdf->SetFont('Calibri');
                    $pdf->Cell(65, 10, $arbeitszeit, 1, 0, 'R');
                    $pdf->SetXY(205, 40);
                    $pdf->SetFont('Calibri', 'B');
                    $pdf->Cell(65, 10, 'AT/Wo.', 1, 0, 'C');
                    $pdf->SetFont('Calibri');
                    $pdf->Cell(65, 10, $arbeitstage, 1, 1, 'R');
                    $pdf->SetXY(205, 50);
                    $pdf->SetFont('Calibri', 'B');
                    $pdf->Cell(65, 10, 'Soll', 1, 0, 'C');
                    $pdf->SetFont('Calibri');
                    $pdf->Cell(65, 10, $soll, 1, 1, 'R');
                    $pdf->SetXY(205, 60);
                    $pdf->SetFont('Calibri', 'B');
                    $pdf->Cell(65, 10, 'Status', 1, 0, 'C');
                    $pdf->SetFont('Calibri');
                    $pdf->Cell(65, 10, $status, 1, 1, 'R');
                } else {
                    for ($i = 0; $i < sizeof($rules); $i++) {
                        /** @var WorkingRule $rule */
                        $rule = $rules[$i];
                        $begin = $rule->validFrom->format('d.m');
                        $end = $rule->validTo ? $rule->validTo->format('d.m') : 'a. W.';
                        $gueltigCaption = $this->handleUmlaut('Gültig');
                        $gueltig = $begin . ' - ' . $end;
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

                        $pdf->SetXY(205 + $i * 130, 30);
                        $pdf->SetFont('Calibri', 'B');
                        $pdf->Cell(65, 10, $gueltigCaption, 1, 0, 'C');
                        $pdf->SetFont('Calibri');
                        $pdf->Cell(65, 10, $gueltig, 1, 0, 'R');

                        $pdf->SetXY(205 + $i * 130, 40);
                        $pdf->SetFont('Calibri', 'B');
                        $pdf->Cell(65, 10, 'WoAz', 1, 0, 'C');
                        $pdf->SetFont('Calibri');
                        $pdf->Cell(65, 10, $arbeitszeit, 1, 0, 'R');
                        $pdf->SetXY(205 + $i * 130, 50);
                        $pdf->SetFont('Calibri', 'B');
                        $pdf->Cell(65, 10, 'AT/Wo.', 1, 0, 'C');
                        $pdf->SetFont('Calibri');
                        $pdf->Cell(65, 10, $arbeitstage, 1, 1, 'R');
                        $pdf->SetXY(205 + $i * 130, 60);
                        $pdf->SetFont('Calibri', 'B');
                        $pdf->Cell(65, 10, 'Soll', 1, 0, 'C');
                        $pdf->SetFont('Calibri');
                        $pdf->Cell(65, 10, $soll, 1, 1, 'R');
                        $pdf->SetXY(205 + $i * 130, 70);
                        $pdf->SetFont('Calibri', 'B');
                        $pdf->Cell(65, 10, 'Status', 1, 0, 'C');
                        $pdf->SetFont('Calibri');
                        $pdf->Cell(65, 10, $status, 1, 1, 'R');
                    }
                }

                // Here starts the month table
                // gather data
                $allMonthDays = DaysOfMonth::get($month);
                $saldoHeader = $this->handleUmlaut('tägl. Abweichung von der Sollzeit');

                // the table head
                $pdf->SetXY(15, 85);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(50, 30, 'Tag', 1, 0, 'C');
                $pdf->Cell(50, 30, 'Beginn', 1, 0, 'C');
                $pdf->Cell(50, 30, 'Ende', 1, 0, 'C');
                $pdf->Cell(50, 30, 'Pause', 1, 0, 'C');
                $pdf->MultiCell(40, 10, "\nMobiles Arbeiten", 1, 'C');
                $pdf->SetXY(255, 85);
                $pdf->MultiCell(65, 10, $saldoHeader, 1, 'C');
                $pdf->SetXY(320, 85);
                $pdf->Cell(65, 30, 'Monatssumme', 1, 0, 'C');
                $pdf->Cell(120, 30, 'Bemerkung', 1, 0, 'C');
                $pdf->Cell(300, 30, 'Anmerkung', 1, 0, 'C');

                // the table body
                $rowIndex = 0;
                $currentPageNumber = 1;
                $monatssumme = Saldo::createFromHoursAndMinutes();
                $timeOffsConfigArray = array_flip(
                    Factory::fromFile('./../server/config/timeOffs.config.php', true)
                        ->toArray()
                );
                $holidays = HolidayService::getHolidays($month->format('Y'));
                $pdf->SetFillColor(200, 200 ,200);
                /** @var DateTime $monthDay */
                foreach ($allMonthDays as $monthDay) {
                    // gather data
                    $day = $this->dayTable->getByUserIdAndDay($userId, $monthDay);
                    $tag = $monthDay->format('j');
                    $fill =  ($monthDay->format('N') == 6 || $monthDay->format('N') == 7);
                    foreach ($holidays as $holiday) {
                        if ($holiday['date'] === $monthDay->format(WorkingDay::DATE_FORMAT)) {
                            $fill = true;
                        }
                    }
                    // print day row
                    $pdf->SetFont('Calibri', 'B');
                    if ($pdf->PageNo() === 1) {
                        $pdf->SetXY(15, 115 + $rowIndex * 10);
                    } else {
                        if ($currentPageNumber !== $pdf->PageNo()) {
                            $currentPageNumber = $pdf->PageNo();
                            $rowIndex = 0;
                        }
                        $pdf->SetXY(15, 38 + $rowIndex * 10);
                    }
                    $pdf->Cell(50, 10, $tag, 1, 0, 'C', $fill);
                    $pdf->SetFont('Calibri');
                    if ($day === null) {
                        $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(40, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(65, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(65, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(120, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(300, 10, '', 1, 0, 'C', $fill);
                        $rowIndex++;
                    } elseif ($day->getDayParts() && sizeof($day->getDayParts()) <= 1) {
                        /** @var WorkingDayPart | null $dayPart */
                        $dayPart = sizeof($day->getDayParts()) === 1 ? $day->getDayParts()[0] : null;
                        $beginn = $dayPart && $dayPart->begin ? $dayPart->begin->format('H:m') : '';
                        $ende = $dayPart && $dayPart->end ? $dayPart->end->format('H:m') : '';
                        $pause = $dayPart->getBreak()->getAbsoluteMinuteString();
                        if ($day->getSaldo()
                            && !($day->getSaldo()->getHours() === 0 && $day->getSaldo()->getMinutes() === 0)) {
                            $uebertrag = $day->getSaldo();
                            $monatssumme = Saldo::getSum($monatssumme, $uebertrag);
                            $monatssummeString = $monatssumme;
                        } else {
                            $uebertrag = '';
                            $monatssummeString = '';
                        }
                        $mobil = $dayPart ? ( $dayPart->begin ? ($dayPart->mobileWorking ? 'Ja' : 'Nein') : '') : '';
                        $bemerkung = $this->handleUmlaut($timeOffsConfigArray[$day->timeOff]);
                        $anmerkung = $this->handleUmlaut($day->comment);
                        $pdf->Cell(50, 10, $beginn, 1, 0, 'C', $fill);
                        $pdf->Cell(50, 10, $ende, 1, 0, 'C', $fill);
                        $pdf->Cell(50, 10, $pause, 1, 0, 'C', $fill);
                        $pdf->Cell(40, 10, $mobil, 1, 0, 'C', $fill);
                        $pdf->Cell(65, 10, $uebertrag, 1, 0, 'C', $fill);
                        $pdf->Cell(65, 10, $monatssummeString, 1, 0, 'C', $fill);
                        $pdf->Cell(120, 10, $bemerkung, 1, 0, 'C', $fill);
                        $oldY = $pdf->GetY();
                        $pdf->MultiCell(300, 10, $anmerkung, 1, 'L', $fill);
                        $y = $pdf->GetY();
                        $lines = ($y - $oldY) / 10;
                        $rowIndex += $lines;
                    } else {
                        if ($day->getSaldo()) {
                            $uebertrag = $day->getSaldo();
                            $monatssumme = Saldo::getSum($uebertrag, $monatssumme);
                            $monatssummeString = $monatssumme;
                        } else {
                            $uebertrag = '';
                            $monatssummeString = '';
                        }
                        $bemerkung = $this->handleUmlaut($timeOffsConfigArray[$day->timeOff]);
                        $anmerkung = $this->handleUmlaut($day->comment);
                        $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(40, 10, '', 1, 0, 'C', $fill);
                        $pdf->Cell(65, 10, $uebertrag, 1, 0, 'C', $fill);
                        $pdf->Cell(65, 10, $monatssummeString, 1, 0, 'C', $fill);
                        $pdf->Cell(120, 10, $bemerkung, 1, 0, 'C', $fill);
                        $oldY = $pdf->GetY();
                        $pdf->MultiCell(300, 10, $anmerkung, 1, 'L', $fill);
                        $y = $pdf->GetY();
                        $lines = ($y - $oldY) / 10;
                        $rowIndex += $lines;
                        /** @var WorkingDayPart $dayPart */
                        foreach ($day->getDayParts() as $dayPart) {
                            $beginn = $dayPart && $dayPart->begin ? $dayPart->begin->format('H:m') : '';
                            $ende = $dayPart && $dayPart->end ? $dayPart->end->format('H:m') : '';
                            $pause = $dayPart->getBreak()->getAbsoluteMinuteString();
                            $mobil = $dayPart->begin ? ($dayPart->mobileWorking ? 'Ja' : 'Nein') : '';
                            $pdf->SetXY(15, 115 + $rowIndex * 10);
                            $pdf->Cell(50, 10, '', 1, 0, 'C', $fill);
                            $pdf->Cell(50, 10, $beginn, 1, 0, 'C', $fill);
                            $pdf->Cell(50, 10, $ende, 1, 0, 'C', $fill);
                            $pdf->Cell(50, 10, $pause, 1, 0, 'C', $fill);
                            $pdf->Cell(40, 10, $mobil, 1, 0, 'C', $fill);
                            $pdf->Cell(65, 10, '', 1, 0, 'C', $fill);
                            $pdf->Cell(65, 10, '', 1, 0, 'C', $fill);
                            $pdf->Cell(120, 10, '', 1, 0, 'C', $fill);
                            $pdf->MultiCell(300, 10, '', 1, 'L', $fill);
                            $rowIndex++;
                        }
                    }
                }

                // footer
                $uebertragCaption = $this->handleUmlaut('Übertrag vom Vormonat');
                $carry = $this->carryTable->getByUserIdAndYear($userId, $month);
                $months = $this->monthTable->getByUserIdAndMonth($userId, $month);
                $uebertrag = $carry->saldo;
                foreach ($months as $monthToAdd) {
                    if ($monthToAdd->month->format('n') !== $month->format('n')) {
                        $uebertrag = Saldo::getSum($uebertrag, $monthToAdd->saldo);
                    }
                }
                $gesamt = Saldo::getSum($uebertrag, $monatssumme);
                $kappungsgrenzeInvert = Saldo::createFromHoursAndMinutes(
                    $kappungsgrenze->getHours(), $kappungsgrenze->getMinutes(), false);
                $diff = Saldo::getSum($gesamt, $kappungsgrenzeInvert);
                $gekappt = $diff->isPositive() ? $kappungsgrenze : $gesamt;
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetXY($x,  $y + 5);
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(100, 10, 'Monatssumme', 1, 0, 'C');
                $pdf->SetFont('Calibri');
                $pdf->Cell(65, 10, $monatssumme, 1, 1, 'C');
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(100, 10, $uebertragCaption, 1, 0, 'C');
                $pdf->SetFont('Calibri');
                $pdf->Cell(65, 10, $uebertrag, 1, 1, 'C');
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(100, 10, 'Gesamtsaldo', 1, 0, 'C');
                $pdf->SetFont('Calibri');
                $pdf->Cell(65, 10, $gesamt, 1, 1, 'C');
                $pdf->SetFont('Calibri', 'B');
                $pdf->Cell(100, 10, 'Saldo incl. Kappung', 1, 0, 'C');
                $pdf->SetFont('Calibri');
                $pdf->Cell(65, 10, $gekappt, 1, 0, 'C');

                $x = $pdf->GetX();
                $y = $pdf->GetY() - 30;
                $pdf->SetXY($x,  $y);
                $pdf->SetFont('Calibri', 'B');
                $pdf->MultiCell(110, 10, "aufgestellt:\n(Mitarbeiter/in)", 0, 'R');
                $pdf->SetXY($x + 110, $y + 10);
                $pdf->Cell(160, 10, "", 'B');
                $pdf->SetXY($x,  $y + 30);
                $pdf->MultiCell(110, 10, "Kenntnis genommen:\n(Vorgesetzte/r)", 0, 'R');
                $y = $pdf->GetY();
                $pdf->SetXY($x + 110,  $y - 10);
                $pdf->Cell(160, 10, "", 'B');

                // add auto-print for firefox
                if ($browser === 'firefox') {
                    $pdf->AutoPrint();
                }
                // write output
                $pdf->Output('F', "public/files/$browser/$filename");
            }

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

    /** @noinspection PhpUnusedLocalVariableInspection */
    private function getFilename(): string {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $charsLength = strlen($chars);
        $filename = '';
        for ($i=0; $i < 10; $i++) {

            try {
                $filename .= $chars[random_int(0, $charsLength - 1)];
            } catch (Exception $ignored) {
            }
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

    private function handleUmlaut(?string $str): string {
        return $str ? iconv('UTF-8', 'windows-1252', $str) : '';
    }
}