<?php
/**
 * SimplePDF - a minimal, dependency-free PDF writer.
 *
 * Generates a single-page, text-only PDF entirely with raw PHP (no Composer
 * packages, no external libraries). Used to produce downloadable donation
 * receipts. Note: stick to plain ASCII text (e.g. "Rs." not "₹") since the
 * built-in Helvetica base font only supports standard/WinAnsi encoding.
 */
class SimplePDF {
    private $lines = [];
    private $pageWidth = 612;
    private $pageHeight = 792;

    public function addLine($text, $size = 12, $x = 50) {
        $this->lines[] = ['text' => $text, 'size' => $size, 'x' => $x];
    }

    public function addSpacer($height = 10) {
        $this->lines[] = ['spacer' => $height];
    }

    private function escape($text) {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string)$text);
    }

    private function buildContentStream() {
        $y = $this->pageHeight - 60;
        $body = "BT\n";
        foreach ($this->lines as $line) {
            if (isset($line['spacer'])) {
                $y -= $line['spacer'];
                continue;
            }
            $size = $line['size'];
            $x = $line['x'];
            $text = $this->escape($line['text']);
            $body .= "/F1 {$size} Tf\n";
            $body .= "1 0 0 1 {$x} {$y} Tm\n";
            $body .= "({$text}) Tj\n";
            $y -= ($size + 6);
        }
        $body .= "ET";
        return $body;
    }

    public function output() {
        $content = $this->buildContentStream();

        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> "
                    . "/MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Contents 5 0 R >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[5] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad($offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    public function save($path) {
        file_put_contents($path, $this->output());
    }

    public function stream($filename = 'receipt.pdf') {
        $data = $this->output();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($data));
        echo $data;
    }
}
