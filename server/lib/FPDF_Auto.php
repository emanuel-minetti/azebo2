<?php
// See: http://www.fpdf.org/?go=script&id=36
namespace AzeboLib;

use Fpdf\Fpdf;

class PDF_JavaScript extends FPDF {

    protected $javascript;
    protected $n_js;

    function IncludeJS($script) {
        $this->javascript = $script;
    }

    function _putjavascript() {
        $this->_newobj();
        $this->n_js = $this->n;
        $this->_put('<<');
        $this->_put('/Names [(EmbeddedJS) '.($this->n+1).' 0 R]');
        $this->_put('>>');
        $this->_put('endobj');
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/S /JavaScript');
        $this->_put('/JS '.$this->_textstring($this->javascript));
        $this->_put('>>');
        $this->_put('endobj');
    }

    function _putresources() {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    function _putcatalog() {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_put('/Names <</JavaScript '.($this->n_js).' 0 R>>');
        }
    }
}

class FPDF_Auto extends PDF_JavaScript {
    function AutoPrint($printer='')
    {
        // Open the print dialog
        if($printer)
        {
            $printer = str_replace('\\', '\\\\', $printer);
            $script = "var pp = getPrintParams();";
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
            $script .= "pp.printerName = '$printer'";
            $script .= "print(pp);";
        }
        else
            $script = 'print(true);';
        $this->IncludeJS($script);
    }
}


