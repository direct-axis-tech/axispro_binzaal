<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/

/**
 * Document blueprint use following parameters set in doctext.inc:
 *
 * $Addr1,    $Addr2 - address info
 * $Payment_Terms - payment terms line
 * $Footer - footer texts
 * $this->company - company info
 * $this->title - report title
 * $this->formData - some other info
 ***/

$this->row = $this->pageHeight - $this->topMargin - 20; //FOR NYPD ONLY

$upper = $this->row - 2 * $this->lineHeight;
$lower = $this->bottomMargin + 8 * $this->lineHeight;
$iline1 = $upper - 7.5 * $this->lineHeight;
$iline2 = $iline1 - 6 * $this->lineHeight;//Border lines
$iline3 = $iline2 - 1.5 * $this->lineHeight;
$iline4 = $iline3 - 1.5 * $this->lineHeight;
$iline5 = $iline4 - 2 * $this->lineHeight;
$iline6 = $iline5 - 1.5 * $this->lineHeight;
$iline7 = $lower;
$right = $this->pageWidth - $this->rightMargin;
$width = ($right - $this->leftMargin) / 5;
$icol = $this->pageWidth / 3;
$ccol = $this->cols[0] + 4;
$c2col = $ccol + 60;
$ccol2 = $icol / 2;
$mcol = $icol + 8;
$mcol2 = $this->pageWidth - $ccol2;
$cols = count($this->cols);
$this->SetDrawColor(205, 205, 205);
//		$this->Line($iline1, 1);
$this->SetDrawColor(128, 128, 128);
//		$this->Line($iline1);
$this->rectangle($this->leftMargin, $iline2, $right - $this->leftMargin, $iline2 - $iline3, "F", null, array(222, 231, 236));
$this->Line($iline2);
$this->Line($iline3);
$this->Line($iline4);
$this->rectangle($this->leftMargin, $iline5, $right - $this->leftMargin, $iline5 - $iline6, "F", null, array(222, 231, 236));
$this->Line($iline5);
$this->Line($iline6);
$this->Line($iline7);
$this->LineTo($this->leftMargin, $iline2, $this->leftMargin, $iline4);
$col = $this->leftMargin;
for ($i = 0; $i < 5; $i++) {
    $this->LineTo($col += $width, $iline2, $col, $iline4);
}
$this->LineTo($this->leftMargin, $iline5, $this->leftMargin, $iline7);

// Column lines: Bipin - 06-01-2023
$this->LineTo($this->cols[$cols - 3] + 4, $iline5, $this->cols[$cols - 3] + 4, $iline7);
$this->LineTo($this->cols[$cols - 4] + 4, $iline5, $this->cols[$cols - 4] + 4, $iline7);
$this->LineTo($this->cols[$cols - 5] + 4, $iline5, $this->cols[$cols - 5] + 4, $iline7);
$this->LineTo($this->cols[$cols - 6] + 1, $iline5, $this->cols[$cols - 6] + 1, $iline7);

if ($this->l['a_meta_dir'] == 'rtl') // avoid line overwrite in rtl language
    $this->LineTo($this->cols[$cols - 2], $iline5, $this->cols[$cols - 2], $iline7);
else
    $this->LineTo($this->cols[$cols - 2] + 4, $iline5, $this->cols[$cols - 2] + 4, $iline7);
$this->LineTo($right, $iline5, $right, $iline7);

// Company Logo
$logo = pdf_header_path($this->formData['dimension'], $this->formData['doctype']);

if ($this->company['coy_logo'] != '' && file_exists($logo)) {
    $this->AddImage($logo, $ccol, 700, 515, 80);
} else {
    $this->fontSize += 4;
    $this->Font('bold');
    $this->Text($ccol, $this->company['coy_name'], $icol);
    $this->Font();
    $this->fontSize -= 4;
}
$this->row = 680;

// Document title
$this->SetTextColor(190, 190, 190);
$this->fontSize += 5;
$this->Font('bold');
$this->NewLine();
$this->TextWrap($mcol, $this->row, $this->pageWidth - $this->rightMargin - $mcol - 20, $this->title, 'right');
$this->Font();
$this->fontSize -= 5;
// $this->NewLine();
$this->SetTextColor(0, 0, 0);
$adrline = $this->row;

// Company data
$this->TextWrapLines($ccol, $icol, $this->company['postal_address']);

//$this->NewLine();

$this->Font('italic');
if (@$this->company['phone']) {
    $this->Text($ccol, trans("Phone"), $c2col);
    $this->Text($c2col, $this->company['phone'], $mcol);
    $this->NewLine();
}
if (@$this->company['fax']) {
    $this->Text($ccol, trans("Fax"), $c2col);
    $this->Text($c2col, $this->company['fax'], $mcol);
    $this->NewLine();
}
if (@$this->company['email']) {
    $this->Text($ccol, trans("Email"), $c2col);

    $url = "mailto:" . $this->company['email'];
    $this->SetTextColor(0, 0, 255);
    $this->Text($c2col, $this->company['email'], $mcol);
    $this->SetTextColor(0, 0, 0);
    $this->addLink($url, $c2col, $this->row, $mcol, $this->row + $this->lineHeight);

    $this->NewLine();
}
if (@$this->company['gst_no']) {
//			$this->Text($ccol, trans("Our VAT No."), $c2col);
//			$this->Text($c2col, $this->company['gst_no'], $mcol);
    $this->NewLine();
}
if (@$this->formData['domicile']) {
    $this->Text($ccol, trans("Domicile"), $c2col);
    $this->Text($c2col, $this->company['domicile'], $mcol);
    $this->NewLine();
}
$this->Font();
$this->row = $adrline;
$this->NewLine(4);
$this->Text($this->pageWidth - $this->rightMargin - 150, trans("Date"));
$this->Text($this->pageWidth - $this->rightMargin - 80, sql2date($this->formData['document_date']) . " " . $this->formData['created_time']);

$this->NewLine();
$this->Text($this->pageWidth - $this->rightMargin - 150, $this->formData['document_name']);
$this->Text($this->pageWidth - $this->rightMargin - 80, $this->formData['document_number']);
$this->NewLine(2);

if ($this->pageNumber > 1)
    $this->Text($this->pageWidth - $this->rightMargin - 80, trans("Page") . ' ' . $this->pageNumber);
$this->row = $iline1 - $this->lineHeight;
$this->fontSize -= 2;
$this->Text($ccol, $Addr1['title'], $icol);
$this->Text($mcol, $Addr2['title']);
$this->fontSize += 2;
//
//// address1
///

$padding = 2;
$this->fontSize -= 2;
$temp = $this->row = $this->row - $this->lineHeight - 5;
$this->Text($ccol, $Addr1['name'], $icol);
$this->NewLine();
$this->TextWrapLines($ccol, $icol - $ccol - $padding, $Addr1['address']);
//
//// address2

$this->row = $temp;
$this->Text($mcol, $Addr2['name']);
$this->NewLine();
$this->TextWrapLines($mcol, $this->rightMargin - $mcol - $padding, $Addr2['address'], 'left', 0, 0, NULL, 1);
$this->fontSize += 2;
// Auxiliary document information
$col = $this->leftMargin;
foreach ($aux_info as $info_header => $info_content) {
    $this->row = $iline2 - $this->lineHeight - 1;
    $this->TextWrap($col, $this->row, $width, $info_header, 'C');
    $this->row = $iline3 - $this->lineHeight - 1;
    $this->TextWrap($col, $this->row, $width, $info_content, 'C');
    $col += $width;
}
// Payment terms
$this->row -= (2 * $this->lineHeight);
$this->Font('italic');
//		$this->TextWrap($ccol, $this->row, $right-$ccol, $Payment_Terms);
$this->Font();

// Line headers
$this->row = $iline5 - $this->lineHeight - 1;
$this->Font('bold');
$count = count($this->headers);
$this->cols[$count] = $right - 3;
for ($i = 0; $i < $count; $i++)
    $this->TextCol($i, $i + 1, $this->headers[$i], -2);
$this->Font();

// Footer
$this->Font('italic');
$this->row = $iline7 - $this->lineHeight - 6;


//display_error(print_r($Footer,true));


foreach ($Footer as $line => $txt) {
    if (!is_numeric($line))    // title => link
    {
        $this->fontSize -= 2;
        $this->TextWrap($ccol, $this->row, $right - $ccol, $line, 'C');
        $this->row -= $this->lineHeight;
        $this->SetTextColor(0, 0, 255);
        $this->TextWrap($ccol, $this->row, $right - $ccol, $txt, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->addLink($txt, $ccol, $this->row, $this->pageWidth - $this->rightMargin, $this->row + $this->lineHeight);
        $this->fontSize += 2;
    } else
        $this->TextWrap($ccol, $this->row, $right - $ccol, $txt, 'C');
    $this->row -= $this->lineHeight;

}


//$this->NewLine();
//$this->NewLine();
//$this->NewLine();
//$this->NewLine();


//Footer

$footer_image = pdf_footer_path($this->formData['dimension'], $this->formData['doctype']);

$this->AddImage($footer_image, $ccol, 20, 515, 100);

$this->Font();
$temp = $iline6 - $this->lineHeight - 2;
