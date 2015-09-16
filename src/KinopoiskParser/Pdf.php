<?php
namespace KinopoiskParser;

use Dompdf\Dompdf;

class Pdf
{
    /**
     * @var \Dompdf\Dompdf
     */
    private $pdf;

    public function __construct($html)
    {
        $this->pdf = new Dompdf();
        $this->pdf->set_option('enable_remote', true);
        //$html = file_get_contents(__DIR__ . '/../../71065-render.html');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
    }

    /**
     * @return \Dompdf\Dompdf
     */
    public function getPdf()
    {
        return $this->pdf;
    }
}
