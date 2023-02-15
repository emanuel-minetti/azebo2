<?php /** @noinspection PhpUnused */

namespace Print\Controller;

use AzeboLib\ApiController;
use Carry\Model\WorkingMonthTable;
use DateTime;
use Fpdf\Fpdf;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Service\AuthorizationService;
use Service\log\AzeboLog;
use WorkingTime\Model\WorkingDay;

class PrintController extends ApiController {
    private WorkingMonthTable $monthTable;
    public function __construct(AzeboLog $logger, WorkingMonthTable $monthTable) {
        $this->monthTable = $monthTable;
        parent::__construct($logger);
    }
    public function printAction(): JsonModel|Response{
        $this->prepare();
        $yearParam = $this->params('year');
        $monthParam = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ["POST"])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            $workingMonth = $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0];
            if (!$workingMonth->finalized) {
                $workingMonth->finalized = true;
                $this->monthTable->update($workingMonth);
            }

            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',16);
            $pdf->Cell(40,10,'Hello World!');
            $pdf->Output('F', 'public/files/test.pdf');
            $result = [
                'file' => "test.pdf",
            ];
            return $this->processResult($result, 0);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }

}