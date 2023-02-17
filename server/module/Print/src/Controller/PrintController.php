<?php /** @noinspection PhpUnused */

namespace Print\Controller;

use AzeboLib\ApiController;
use Carry\Model\WorkingMonthTable;
use DateTime;
use Exception;
use Fpdf\Fpdf;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Login\Model\UserTable;
use Service\AuthorizationService;
use Service\log\AzeboLog;
use WorkingTime\Model\WorkingDay;

class PrintController extends ApiController {
    private WorkingMonthTable $monthTable;
    private UserTable $userTable;
    public function __construct(
        AzeboLog $logger,
        WorkingMonthTable $monthTable,
        UserTable $userTable
    ) {
        $this->monthTable = $monthTable;
        $this->userTable = $userTable;
        parent::__construct($logger);
    }
    public function printAction(): JsonModel|Response{
        $this->prepare();
        $yearParam = $this->params('year');
        $monthParam = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ["POST"])) {

            // gather data
            $userId = $this->httpRequest->getQuery()->user_id;
            $workingMonth = $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0];
            $filename = $this->getFilename();
            $todayString = (new DateTime())->format('d/m/Y');
            $user = $this->userTable->find($userId);
            $name = $user->getFullName();

            // finalize month
            if (!$workingMonth->finalized) {
                $workingMonth->finalized = true;
                $this->monthTable->update($workingMonth);
            }

            // write PDF
            $pdf = new FPDF('L', 'pt');
            $pdf->AddFont('Calibri', '', 'calibri.php');
            $pdf->AddFont('Calibri', 'B', 'calibrib.php');
            $pdf->SetFont('Calibri','B',8.5);
            $pdf->AddPage();
            $pdf->SetXY(680, 0);
            $pdf->Cell(40,10,"Zeiterfassungsbogen - Stand $todayString");
            $pdf->SetXY(30, 30);
            $pdf->Cell(30, 10, 'Name', 1);
            $pdf->SetFont('Calibri', '');
            $pdf->Cell(120, 10, $name, 1);
            $pdf->SetFont('Calibri', 'B');
            $pdf->Cell(50, 10, 'WoAz', 1, 0, 'C');
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


}