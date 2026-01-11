<?php
/*******************************************************************************
* FPDF                                                                         *
*
* Version: 1.86                                                                *
* Date:    2023-06-25                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

class FPDF
{
const VERSION = '1.86';

protected $page;               // current page number
protected $n;                  // current object number
protected $offsets;            // array of object offsets
protected $buffer;             // buffer holding in-memory PDF
protected $pages;              // array containing pages
protected $state;              // current document state
protected $compress;           // compression flag
protected $iconv;              // whether iconv is available
protected $k;                  // scale factor (number of points in user unit)
protected $DefOrientation;     // default orientation
protected $CurOrientation;     // current orientation
protected $StdPageSizes;       // standard page sizes
protected $DefPageSize;        // default page size
protected $CurPageSize;        // current page size
protected $CurRotation;        // current page rotation
protected $PageInfo;           // page-related data
protected $wPt, $hPt;          // dimensions of current page in points
protected $w, $h;              // dimensions of current page in user unit
protected $lMargin;            // left margin
protected $tMargin;            // top margin
protected $rMargin;            // right margin
protected $bMargin;            // page break margin
protected $cMargin;            // cell margin
protected $x, $y;              // current position in user unit
protected $lasth;              // height of last printed cell
protected $LineWidth;          // line width in user unit
protected $fontpath;           // directory containing fonts
protected $CoreFonts;          // array of core font names
protected $fonts;              // array of used fonts
protected $FontFiles;          // array of font files
protected $encodings;          // array of encodings
protected $cmaps;              // array of ToUnicode CMaps
protected $FontFamily;         // current font family
protected $FontStyle;          // current font style
protected $underline;          // underlining flag
protected $CurrentFont;        // current font info
protected $FontSizePt;         // current font size in points
protected $FontSize;           // current font size in user unit
protected $DrawColor;          // commands for drawing color
protected $FillColor;          // commands for filling color
protected $TextColor;          // commands for text color
protected $ColorFlag;          // indicates whether fill and text colors are different
protected $WithAlpha;           // indicates whether alpha channel is used
protected $ws;                 // word spacing
protected $nbevents;           // number of events
protected $AutoPageBreak;      // automatic page break
protected $PageBreakTrigger;   // threshold to trigger page break
protected $InHeader;           // flag set when processing header
protected $InFooter;           // flag set when processing footer
protected $ZoomMode;           // zoom display mode
protected $LayoutMode;         // layout display mode
protected $title;              // title
protected $subject;            // subject
protected $author;             // author
protected $keywords;           // keywords
protected $creator;            // creator
protected $AliasNbPages;       // alias for total number of pages
protected $PDFVersion;         // PDF version number

/*******************************************************************************
*                               Public methods                                 *
*******************************************************************************/

function __construct($orientation='P', $unit='mm', $size='A4')
{
	// Some checks
	$this->_dochecks();
	// Initialization of properties
	$this->page = 0;
	$this->n = 2;
	$this->buffer = '';
	$this->pages = array();
	$this->PageInfo = array();
	$this->state = 0;
	$this->fonts = array();
	$this->FontFiles = array();
	$this->encodings = array();
	$this->cmaps = array();
	$this->ws = 0;
	$this->nbevents = 0;
	$this->WithAlpha = false;
	$this->AliasNbPages = false;
	$this->ZoomMode = 'fullpage';
	$this->LayoutMode = 'single';
	$this->PDFVersion = '1.3';
	// Font path
	if(defined('FPDF_FONTPATH'))
	{
		$this->fontpath = FPDF_FONTPATH;
		if(substr($this->fontpath, -1)!='/' && substr($this->fontpath, -1)!='\')
			$this->fontpath .= '/';
	}
	elseif(is_dir(dirname(__FILE__).'/font'))
		$this->fontpath = dirname(__FILE__).'/font/';
	else
		$this->fontpath = '';
	// Core fonts
	$this->CoreFonts = array('courier'=>'Courier', 'courierB'=>'Courier-Bold', 'courierI'=>'Courier-Oblique', 'courierBI'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica', 'helveticaB'=>'Helvetica-Bold', 'helveticaI'=>'Helvetica-Oblique', 'helveticaBI'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman', 'timesB'=>'Times-Bold', 'timesI'=>'Times-Italic', 'timesBI'=>'Times-BoldItalic',
		'symbol'=>'Symbol', 'zapfdingbats'=>'ZapfDingbats');
	// Standard page sizes
	$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
		'letter'=>array(612,792), 'legal'=>array(612,1008));
	// Scale factor
	if($unit=='pt')
		$this->k = 1;
	elseif($unit=='mm')
		$this->k = 72/25.4;
	elseif($unit=='cm')
		$this->k = 72/2.54;
	elseif($unit=='in')
		$this->k = 72;
	else
		$this->Error('Incorrect unit: '.$unit);
	// Page sizes
	$this->DefPageSize = $this->_getpagesize($size);
	$this->CurPageSize = $this->DefPageSize;
	// Page orientation
	$orientation = strtolower($orientation);
	if($orientation=='p' || $orientation=='portrait')
	{
		$this->DefOrientation = 'P';
		$this->CurOrientation = 'P';
	}
	elseif($orientation=='l' || $orientation=='landscape')
	{
		$this->DefOrientation = 'L';
		$this->CurOrientation = 'L';
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	// mPDF 5.7.1
	$this->iconv = function_exists('iconv');
	// Set default display modes
	$this->SetDisplayMode($this->ZoomMode, $this->LayoutMode);
	// Set default lower case for font names
	foreach($this->CoreFonts as $key => $value)
		$this->CoreFonts[$key] = strtolower($value);
}

function SetMargins($left, $top, $right=-1)
{
	// Set left, top and right margins
	$this->lMargin = $left*$this->k;
	$this->tMargin = $top*$this->k;
	if($right==-1)
		$right = $left;
	$this->rMargin = $right*$this->k;
}

function SetLeftMargin($margin)
{
	// Set left margin
	$this->lMargin = $margin*$this->k;
	if($this->page>0 && $this->x<$this->lMargin)
		$this->x = $this->lMargin;
}

function SetTopMargin($margin)
{
	// Set top margin
	$this->tMargin = $margin*$this->k;
}

function SetRightMargin($margin)
{
	// Set right margin
	$this->rMargin = $margin*$this->k;
}

function SetAutoPageBreak($auto, $margin=0)
{
	// Set auto page break mode and threshold
	$this->AutoPageBreak = $auto;
	$this->bMargin = $margin*$this->k;
	$this->PageBreakTrigger = $this->h-$this->bMargin;
}

function SetDisplayMode($zoom, $layout='single')
{
	// Set display mode in viewer
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode = $zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode = $layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	// Set page compression
	$this->compress = $compress;
}

function SetTitle($title, $isUTF8=false)
{
	// Title of document
	$this->title = $isUTF8 ? $this->_UTF8toUTF16($title) : $title;
}

function SetSubject($subject, $isUTF8=false)
{
	// Subject of document
	$this->subject = $isUTF8 ? $this->_UTF8toUTF16($subject) : $subject;
}

function SetAuthor($author, $isUTF8=false)
{
	// Author of document
	$this->author = $isUTF8 ? $this->_UTF8toUTF16($author) : $author;
}

function SetKeywords($keywords, $isUTF8=false)
{
	// Keywords of document
	$this->keywords = $isUTF8 ? $this->_UTF8toUTF16($keywords) : $keywords;
}

function SetCreator($creator, $isUTF8=false)
{
	// Creator of document
	$this->creator = $isUTF8 ? $this->_UTF8toUTF16($creator) : $creator;
}

function AliasNbPages($alias='{nb}')
{
	// Define an alias for total number of pages
	$this->AliasNbPages = $alias;
}

function Error($msg)
{
	// Fatal error
	die('<b>FPDF error:</b> '.$msg);
}

function Open()
{
	// Begin document
	$this->state = 1;
}

function Close()
{
	// Terminate document
	if($this->state==3)
		return;
	if($this->page==0)
		$this->AddPage();
	// Page footer
	$this->InFooter = true;
	$this->Footer();
	$this->InFooter = false;
	// Close page
	$this->_endpage();
	// Close document
	$this->_enddoc();
}

function AddPage($orientation='', $size='', $rotation=0)
{
	// Start a new page
	if($this->state==0)
		$this->Open();
	$family = $this->FontFamily;
	$style = $this->FontStyle.($this->underline ? 'U' : '');
	$fontsize = $this->FontSizePt;
	$lw = $this->LineWidth;
	$dc = $this->DrawColor;
	$fc = $this->FillColor;
	$tc = $this->TextColor;
	$cf = $this->ColorFlag;
	if($this->page>0)
	{
		// Page footer
		$this->InFooter = true;
		$this->Footer();
		$this->InFooter = false;
		// Close page
		$this->_endpage();
	}
	// Start new page
	$this->page++;
	$this->pages[$this->page] = '';
	$this->state = 2;
	$this->CurOrientation = $orientation=='' ? $this->DefOrientation : $orientation;
	$this->CurPageSize = $size=='' ? $this->DefPageSize : $this->_getpagesize($size);
	$this->CurRotation = $rotation;
	$this->PageInfo[$this->page]['size'] = $this->CurPageSize;
	$this->PageInfo[$this->page]['ori'] = $this->CurOrientation;
	$this->PageInfo[$this->page]['rotation'] = $this->CurRotation;
	if($this->CurOrientation=='P')
	{
		$this->w = $this->CurPageSize[0];
		$this->h = $this->CurPageSize[1];
	}
	else
	{
		$this->w = $this->CurPageSize[1];
		$this->h = $this->CurPageSize[0];
	}
	$this->wPt = $this->w*$this->k;
	$this->hPt = $this->h*$this->k;
	$this->PageBreakTrigger = $this->h-$this->bMargin;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->lasth = 0;
	// Page header
	$this->InHeader = true;
	$this->Header();
	$this->InHeader = false;
	// Restore line width
	if($lw)
	{
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w', $lw*$this->k));
	}
	// Restore font
	if($family)
		$this->SetFont($family, $style, $fontsize);
	// Restore colors
	if($dc!='0 G')
		$this->SetDrawColor(substr($dc, 0, strpos($dc, ' ')), substr($dc, strpos($dc, ' ')+1, strpos($dc, ' ', strpos($dc, ' ')+1)-strpos($dc, ' ')-1), substr($dc, strpos($dc, ' ', strpos($dc, ' ')+1)+1, strpos($dc, ' ', strpos($dc, ' ', strpos($dc, ' ')+1)+1)-strpos($dc, ' ', strpos($dc, ' ', strpos($dc, ' ')+1)+1)-1));
	if($fc!='0 g')
		$this->SetFillColor(substr($fc, 0, strpos($fc, ' ')), substr($fc, strpos($fc, ' ')+1, strpos($fc, ' ', strpos($fc, ' ')+1)-strpos($fc, ' ')-1), substr($fc, strpos($fc, ' ', strpos($fc, ' ')+1)+1, strpos($fc, ' ', strpos($fc, ' ', strpos($fc, ' ')+1)+1)-strpos($fc, ' ', strpos($fc, ' ', strpos($fc, ' ')+1)+1)-1));
	if($tc!='0 0 0 rg')
		$this->SetTextColor(substr($tc, 0, strpos($tc, ' ')), substr($tc, strpos($tc, ' ')+1, strpos($tc, ' ', strpos($tc, ' ')+1)-strpos($tc, ' ')-1), substr($tc, strpos($tc, ' ', strpos($tc, ' ')+1)+1, strpos($tc, ' ', strpos($tc, ' ', strpos($tc, ' ')+1)+1)-strpos($tc, ' ', strpos($tc, ' ', strpos($tc, ' ')+1)+1)-1));
	$this->ColorFlag = $cf;
}

function Header()
{
	// To be implemented in your own inherited class
}

function Footer()
{
	// To be implemented in your own inherited class
}

function PageNo()
{
	// Get current page number
	return $this->page;
}

function SetDrawColor($r, $g=-1, $b=-1)
{
	// Set the color for stroking operations
	if(($r==0 && $g==0 && $b==0) || $g==-1)
		$this->DrawColor = sprintf('%.3F G', $r/255);
	else
		$this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r/255, $g/255, $b/255);
	if($this->page>0)
		$this->_out($this->DrawColor);
}

function SetFillColor($r, $g=-1, $b=-1)
{
	// Set the color for filling operations
	if(($r==0 && $g==0 && $b==0) || $g==-1)
		$this->FillColor = sprintf('%.3F g', $r/255);
	else
		$this->FillColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
	if($this->page>0)
		$this->_out($this->FillColor);
}

function SetTextColor($r, $g=-1, $b=-1)
{
	// Set the color for text
	if(($r==0 && $g==0 && $b==0) || $g==-1)
		$this->TextColor = sprintf('%.3F g', $r/255);
	else
		$this->TextColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
}

function GetStringWidth($s)
{
	// Get width of a string in the current font
	$s = (string)$s;
	$cw = &$this->CurrentFont['cw'];
	$w = 0;
	$l = strlen($s);
	for($i=0; $i<$l; $i++)
		$w += $cw[$s[$i]];
	return $w*$this->FontSize/1000;
}

function SetLineWidth($width)
{
	// Set line width
	$this->LineWidth = $width;
	if($this->page>0)
		$this->_out(sprintf('%.2F w', $width*$this->k));
}

function Line($x1, $y1, $x2, $y2)
{
	// Draw a line
	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1*$this->k, ($this->h-$y1)*$this->k, $x2*$this->k, ($this->h-$y2)*$this->k));
}

function Rect($x, $y, $w, $h, $style='')
{
	// Draw a rectangle
	if($style=='F')
		$op = 'f';
	elseif($style=='FD' || $style=='DF')
		$op = 'B';
	else
		$op = 'S';
	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s', $x*$this->k, ($this->h-$y)*$this->k-$h*$this->k, $w*$this->k, $h*$this->k, $op));
}

function AddFont($family, $style='', $file='', $uni=false)
{
	// Add a TrueType or Type1 font
	$family = strtolower($family);
	if($file=='')
		$file = str_replace(' ', '', $family).strtolower($style).'.php';
	$style = strtoupper($style);
	if($style=='IB')
		$style = 'BI';
	$fontkey = $family.$style;
	if(isset($this->fonts[$fontkey]))
		return;
	$info = $this->_loadfont($file);
	$info['name'] = $family;
	$info['type'] = $uni ? 'TTF' : 'Type1';
	$info['desc']['Style'] = $style;
	$this->fonts[$fontkey] = $info;
}

function SetFont($family, $style='', $size=0)
{
	// Select a font; size given in points
	if($family=='')
		$family = $this->FontFamily;
	else
		$family = strtolower($family);
	$style = strtoupper($style);
	if(strpos($style, 'U')!==false)
	{
		$this->underline = true;
		$style = str_replace('U', '', $style);
	}
	else
		$this->underline = false;
	if($style=='IB')
		$style = 'BI';
	if($size==0)
		$size = $this->FontSizePt;
	// Test if font is already selected
	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
		return;
	// Test if font is already loaded
	$fontkey = $family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		// Test if one of the core fonts
		if($family=='arial')
			$family = 'helvetica';
		if(in_array($family, $this->CoreFonts))
		{
			if($family=='symbol' || $family=='zapfdingbats')
				$style = '';
			$fontkey = $family.$style;
			if(!isset($this->fonts[$fontkey]))
				$this->AddFont($family, $style);
		}
		else
			$this->Error('Undefined font: '.$family.' '.$style);
	}
	// Select it
	$this->FontFamily = $family;
	$this->FontStyle = $style;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	$this->CurrentFont = &$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
}

function SetFontSize($size)
{
	// Set font size in points
	if($this->FontSizePt==$size)
		return;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
}

function AddLink()
{
	// Create a new internal link
	$n = count($this->PageInfo)+1;
	$this->PageInfo[$n] = array('size'=>$this->CurPageSize, 'ori'=>$this->CurOrientation, 'rotation'=>$this->CurRotation);
	return $n;
}

function SetLink($link, $y=0, $page=-1)
{
	// Set destination of internal link
	if($y==-1)
		$y = $this->y;
	if($page==-1)
		$page = $this->page;
	$this->PageInfo[$page]['links'][] = array($this->x*$this->k, ($this->h-$y)*$this->k, $link);
}

function Link($x, $y, $w, $h, $link)
{
	// Put a link on the page
	$this->PageInfo[$this->page]['annots'][] = array('rect'=>sprintf('%.2F %.2F %.2F %.2F', $x*$this->k, ($this->h-$y)*$this->k-$h*$this->k, $w*$this->k, $h*$this->k), 'link'=>$link);
}

function Text($x, $y, $txt)
{
	// Output a string
	$txt = (string)$txt;
	$s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x*$this->k, ($this->h-$y)*$this->k, $this->_escape($txt));
	if($this->underline && $txt!='')
		$s .= ' '.$this->_dounderline($x, $y, $txt);
	if($this->ColorFlag)
		$s = 'q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function AcceptPageBreak()
{
	// Accept automatic page break or not
	return $this->AutoPageBreak;
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
	// Output a cell
	$txt = (string)$txt;
	$k = $this->k;
	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		// Automatic page break
		$x = $this->x;
		$ws = $this->ws;
		if($ws>0)
		{
			$this->ws = 0;
			$this->_out('0 Tw');
		}
		$this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
		$this->x = $x;
		if($ws>0)
		{
			$this->ws = $ws;
			$this->_out(sprintf('%.3F Tw', $ws*$k));
		}
	}
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$s = '';
	if($fill || $border==1)
	{
		if($fill)
			$op = ($border==1) ? 'B' : 'f';
		else
			$op = 'S';
		$s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$this->k, ($this->h-$this->y)*$this->k-$h*$this->k, $w*$this->k, $h*$this->k, $op);
	}
	if(is_string($border))
	{
		$x = $this->x;
		$y = $this->y;
		if(strpos($border, 'L')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$this->k, ($this->h-$y)*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
		if(strpos($border, 'T')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$this->k, ($this->h-$y)*$this->k, ($x+$w)*$this->k, ($this->h-$y)*$this->k);
		if(strpos($border, 'R')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x+$w)*$this->k, ($this->h-$y)*$this->k, ($x+$w)*$this->k, ($this->h-($y+$h))*$this->k);
		if(strpos($border, 'B')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$this->k, ($this->h-($y+$h))*$this->k, ($x+$w)*$this->k, ($this->h-($y+$h))*$this->k);
	}
	if($txt!='')
	{
		if($align=='R')
			$dx = $w-$this->cMargin-$this->GetStringWidth($txt);
		elseif($align=='C')
			$dx = ($w-$this->GetStringWidth($txt))/2;
		else
			$dx = $this->cMargin;
		if($this->ColorFlag)
			$s .= 'q '.$this->TextColor.' ';
		$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x+$dx)*$this->k, ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$this->k, $this->_escape($txt));
		if($this->underline)
			$s .= ' '.$this->_dounderline($this->x+$dx, $this->y+.5*$h+.3*$this->FontSize, $txt);
		if($this->ColorFlag)
			$s .= ' Q';
		if($link)
			$this->Link($this->x+$dx, $this->y+.5*$h-.5*$this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
	}
	if($s)
		$this->_out($s);
	$this->lasth = $h;
	if($ln>0)
	{
		// Go to next line
		$this->y += $h;
		if($ln==1)
			$this->x = $this->lMargin;
	}
	else
		$this->x += $w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
	// Output text with automatic word-wrap
	$txt = (string)$txt;
	if(!isset($this->CurrentFont['cw']))
		$this->Error('No font selected');
	$cw = &$this->CurrentFont['cw'];
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
	$s = str_replace("\r", '', $txt);
	$nb = strlen($s);
	if($nb>0 && $s[$nb-1]=="\n")
		$nb--;
	$b = 0;
	if($border)
	{
		if($border==1)
		{
			$border = 'LTRB';
			$b = 'LRTB';
		}
		else
		{
			$b = '';
			if(strpos($border, 'L')!==false)
				$b .= 'L';
			if(strpos($border, 'R')!==false)
				$b .= 'R';
			if(strpos($border, 'T')!==false)
				$b .= 'T';
			if(strpos($border, 'B')!==false)
				$b .= 'B';
		}
	}
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$ns = 0;
	$nl = 1;
	while($i<$nb)
	{
		// Get next character
		$c = $s[$i];
		if($c=="\n")
		{
			if($this->ws>0)
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			$this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
			$j = $i+1;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border && $nl==2)
				$b = str_replace('T', '', $b);
			$i++;
			continue;
		}
		if($c==' ')
		{
			$sep = $i;
			$ls = $l;
			$ns++;
		}
		$l += $cw[$c];
		if($l>$wmax)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($i==$j)
					$i++;
				if($this->ws>0)
				{
					$this->ws = 0;
					$this->_out('0 Tw');
				}
				$this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
			}
			else
			{
				if($align=='J')
				{
					$this->ws = ($wmax-$ls)/$ns;
					$this->_out(sprintf('%.3F Tw', $this->ws*$this->k));
				}
				$this->Cell($w, $h, substr($s, $j, $sep-$j), $b, 2, $align, $fill);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border && $nl==2)
				$b = str_replace('T', '', $b);
		}
		else
			$i++;
	}
	// Last chunk
	if($this->ws>0)
	{
		$this->ws = 0;
		$this->_out('0 Tw');
	}
	if($border && strpos($b, 'B')!==false)
		$b = str_replace('B', '', $b);
	$this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
	$this->x = $this->lMargin;
}

function Write($h, $txt, $link='')
{
	// Output text in flowing mode
	$txt = (string)$txt;
	if(!isset($this->CurrentFont['cw']))
		$this->Error('No font selected');
	$cw = &$this->CurrentFont['cw'];
	$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
	$s = str_replace("\r", '', $txt);
	nb = strlen($s);
	$sep = -1;
	i = 0;
	j = 0;
	l = 0;
	nl = 1;
	while($i<$nb)
	{
		// Get next character
		$c = $s[$i];
		if($c=="\n")
		{
			$this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, '', false, $link);
			$j = $i+1;
			$l = 0;
			$sep = -1;
			$nl++;
			$i++;
			continue;
		}
		if($c==' ')
			$sep = $i;
		$l += $cw[$c];
		if($l>$wmax)
		{
			if($sep==-1)
			{
				if($this->x>$this->lMargin)
				{
					// Move to next line
					$this->x = $this->lMargin;
					$this->y += $h;
					$l = 0;
					$sep = -1;
					$nl++;
					continue;
				}
				if($i==$j)
					$i++;
				$this->Cell($w, h, substr($s, $j, $i-$j), 0, 2, '', false, $link);
			}
			else
			{
				$this->Cell($w, $h, substr($s, $j, $sep-$j), 0, 2, '', false, $link);
				$i = $sep+1;
			}
			$j = $i;
			l = 0;
			$sep = -1;
			nl++;
		}
		else
			$i++;
	}
	// Last chunk
	if($i!=$j)
		$this->Cell($w, $h, substr($s, $j, $i-$j), 0, 0, '', false, $link);
}

function Ln($h='')
{
	// Line feed; default value is last cell height
	$this->x = $this->lMargin;
	if(is_string($h))
		$this->y += $this->lasth;
	else
		$this->y += $h;
}

function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='')
{
	// Put an image on the page
	if(!isset($this->PageInfo[$this->page]['images']))
		$this->PageInfo[$this->page]['images'] = array();
	if($file=='')
		$this->Error('Image file name is empty');
	if(!isset($this->images[$file]))
	{
		// First time this image is used
		if($type=='')
		{
			$pos = strrpos($file, '.');
			if(!$pos)
				$this->Error('Image file has no extension and no type was specified: '.$file);
			$type = substr($file, $pos+1);
		}
		$type = strtolower($type);
		if($type=='jpeg')
			$type = 'jpg';
		$mtd = '_parse'.$type;
		if(!method_exists($this, $mtd))
			$this->Error('Unsupported image type: '.$type);
		$info = $this->$mtd($file);
		$info['i'] = count($this->images)+1;
		$this->images[$file] = $info;
	}
	else
		$info = $this->images[$file];
	// Automatic width and height calculation if needed
	if($w==0 && $h==0)
	{
		// Put image at 96 dpi
		$w = -96;
		$h = -96;
	}
	if($w<0)
		$w = -$info['w']*$w/96/$this->k;
	if($h<0)
		$h = -$info['h']*$h/96/$this->k;
	if($w==0)
		$w = $h*$info['w']/$info['h'];
	if($h==0)
		$h = $w*$info['h']/$info['w'];
	// Flowing mode
	if($y=='')
	{
		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
		{
			$x2 = $this->x;
			$this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
			$this->x = $x2;
		}
		$y = $this->y;
		$this->y += $h;
	}
	if($x=='')
		$x = $this->x;
	$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k, $info['i']));
	if($link)
		$this->Link($x, $y, $w, $h, $link);
}

function GetX()
{
	// Get x position
	return $this->x;
}

function SetX($x)
{
	// Set x position
	if($x>=0)
		$this->x = $x;
	else
		$this->x = $this->w+$x;
}

function GetY()
{
	// Get y position
	return $this->y;
}

function SetY($y)
{
	// Set y position and reset x
	$this->x = $this->lMargin;
	if($y>=0)
		$this->y = $y;
	else
		$this->y = $this->h+$y;
}

function SetXY($x, $y)
{
	// Set x and y positions
	$this->SetX($x);
	$this->SetY($y);
}

function Output($name='', $dest='I')
{
	// Output PDF to some destination
	// Finish document if necessary
	if($this->state<3)
		$this->Close();
	// Either send to browser or save to file
	$dest = strtoupper($dest);
	if($dest=='I' || $dest=='D')
	{
		header('Content-Type: application/pdf');
		header('Content-Length: '.strlen($this->buffer));
		header('Content-Disposition: '.($dest=='D' ? 'attachment' : 'inline').'; filename="'.$name.'"');
		header('Cache-Control: private, max-age=0, must-revalidate');
		header('Pragma: public');
		echo $this->buffer;
	}
	elseif($dest=='S')
	{
		return $this->buffer;
	}
	elseif($dest=='F')
	{
		file_put_contents($name, $this->buffer);
	}
	else
	{
		$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}

/*******************************************************************************
*                              Protected methods                               *
*******************************************************************************/

protected function _dochecks()
{
	// Check mbstring extension
	if(!function_exists('mb_strlen'))
		$this->Error('mbstring extension is not enabled');
	// Check Zlib extension
	if(!function_exists('gzcompress'))
		$this->Error('Zlib extension is not enabled');
	// Check GD extension
	if(!function_exists('imagecreatefrompng'))
		$this->Error('GD extension is not enabled');
}

protected function _getpagesize($size)
{
	if(is_string($size))
	{
		$size = strtolower($size);
		if(!isset($this->StdPageSizes[$size]))
			$this->Error('Unknown page size: '.$size);
		$a = $this->StdPageSizes[$size];
		return array($a[0]/$this->k, $a[1]/$this->k);
	}
	else
	{
		if($size[0]>$size[1])
			return array($size[1], $size[0]);
		else
			return $size;
	}
}

protected function _beginpage($orientation, $size, $rotation)
{
	$this->page++;
	$this->pages[$this->page] = '';
	$this->state = 2;
	$this->CurOrientation = $orientation;
	$this->CurPageSize = $size;
	$this->CurRotation = $rotation;
	$this->PageInfo[$this->page]['size'] = $this->CurPageSize;
	$this->PageInfo[$this->page]['ori'] = $this->CurOrientation;
	$this->PageInfo[$this->page]['rotation'] = $this->CurRotation;
	if($this->CurOrientation=='P')
	{
		$this->w = $this->CurPageSize[0];
		$this->h = $this->CurPageSize[1];
	}
	else
	{
		$this->w = $this->CurPageSize[1];
		$this->h = $this->CurPageSize[0];
	}
	$this->wPt = $this->w*$this->k;
	$this->hPt = $this->h*$this->k;
	$this->PageBreakTrigger = $this->h-$this->bMargin;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->lasth = 0;
}

protected function _endpage()
{
	$this->state = 1;
	// End of page contents
	$p = $this->pages[$this->page];
	$this->_put('endstream');
	$this->_put('endobj');
	$this->_put('<<');
	$this->_put('/Type /Page');
	$this->_put('/Parent 1 0 R');
	$this->_put('/Resources 2 0 R');
	if(isset($this->PageInfo[$this->page]['rotation']))
		$this->_put('/Rotate '.$this->PageInfo[$this->page]['rotation']);
	$this->_put('/MediaBox [0 0 '.sprintf('%.2F %.2F', $this->wPt, $this->hPt).']');
	$this->_put('/Contents '.($this->n).' 0 R');
	$this->_put('>>');
	$this->_put('endobj');
	// Annotations
	if(isset($this->PageInfo[$this->page]['annots']))
	{
		foreach($this->PageInfo[$this->page]['annots'] as $annot)
		{
			$this->_put('<<');
			$this->_put('/Type /Annot');
			$this->_put('/Subtype /Link');
			$this->_put('/Rect ['.$annot['rect'].']');
			$this->_put('/Border [0 0 0]');
			$this->_put('/A <<');
			$this->_put('/S /URI');
			$this->_put('/URI ('.$this->_escape($annot['link']).')');
			$this->_put('>>');
			$this->_put('>>');
			$this->_put('endobj');
		}
	}
	// Links
	if(isset($this->PageInfo[$this->page]['links']))
	{
		foreach($this->PageInfo[$this->page]['links'] as $link)
		{
			$this->_put('<<');
			$this->_put('/Type /Annot');
			$this->_put('/Subtype /Link');
			$this->_put('/Rect ['.$link[0].' '.$link[1].' '.($link[0]+1).' '.($link[1]+1).']');
			$this->_put('/Border [0 0 0]');
			$this->_put('/Dest ['.$link[2].' 0 R /Fit]');
			$this->_put('>>');
			$this->_put('endobj');
		}
	}
	// Images
	if(isset($this->PageInfo[$this->page]['images']))
	{
		foreach($this->PageInfo[$this->page]['images'] as $image)
		{
			$this->_put('<<');
			$this->_put('/Type /XObject');
			$this->_put('/Subtype /Image');
			$this->_put('/Width '.$image['w']);
			$this->_put('/Height '.$image['h']);
			$this->_put('/ColorSpace /DeviceRGB');
			$this->_put('/BitsPerComponent 8');
			$this->_put('/Filter /DCTDecode');
			$this->_put('/Length '.strlen($image['data']));
			$this->_put('>>');
			$this->_putstream($image['data']);
			$this->_put('endobj');
		}
	}
	// Add page to buffer
	$this->pages[$this->page] = $p;
}

protected function _enddoc()
{
	$this->state = 3;
	// PDF header
	$this->_put('%PDF-'.$this->PDFVersion);
	$this->_put('%'.chr(128).chr(129).chr(130).chr(131));
	// Objects
	$this->_putpages();
	$this->_putfonts();
	$this->_putimages();
	$this->_putxobjects();
	$this->_putannots();
	$this->_putlinks();
	$this->_putinfo();
	$this->_putcatalog();
	$this->_puttrailer();
}

protected function _putpages()
{
	nb = $this->page;
	if($this->AliasNbPages)
	{
		// Replace alias for total number of pages
		for($i=1; $i<=$nb; $i++)
			$this->pages[$i] = str_replace($this->AliasNbPages, $nb, $this->pages[$i]);
	}
	for($i=1; $i<=$nb; $i++)
	{
		$this->_newobj();
		$this->_put('<<');
		$this->_put('/Type /Page');
		$this->_put('/Parent 1 0 R');
		$this->_put('/Resources 2 0 R');
		if(isset($this->PageInfo[$i]['rotation']))
			$this->_put('/Rotate '.$this->PageInfo[$i]['rotation']);
		$this->_put('/MediaBox [0 0 '.sprintf('%.2F %.2F', $this->PageInfo[$i]['size'][0]*$this->k, $this->PageInfo[$i]['size'][1]*$this->k).']');
		$this->_put('/Contents '.($this->n+1).' 0 R');
		$this->_put('>>');
		$this->_put('endobj');
		// Page content
		$this->_newobj();
		if($this->compress)
			$p = gzcompress($this->pages[$i]);
		else
			$p = $this->pages[$i];
		$this->_put('<<');
		$this->_put('/Length '.strlen($p));
		if($this->compress)
			$this->_put('/Filter /FlateDecode');
		$this->_put('>>');
		$this->_putstream($p);
		$this->_put('endobj');
	}
	// Pages root
	$this->_newobj();
	$this->offsets[1] = $this->bufferlen;
	$this->_put('<<');
	$this->_put('/Type /Pages');
	$this->_put('/Count '.$nb);
	$this->_put('/Kids [');
	for($i=1; $i<=$nb; $i++)
		$this->_put($this->n-($nb-$i)*2-1).' 0 R';
	$this->_put(']');
	$this->_put('>>');
	$this->_put('endobj');
}

protected function _putfonts()
{
	nf = count($this->FontFiles);
	if($nf==0)
		return;
	foreach($this->FontFiles as $file=>$info)
	{
		// Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n'] = $this->n;
		$font = file_get_contents($this->fontpath.$file, true);
		if(!$font)
			$this->Error('Font file not found: '.$file);
		$compressed = (substr($file, -2)=='.z');
		if(!$compressed && isset($info['length2']))
		{
			$header = (ord($font[0])==128);
			if($header)
			{
				// Strip first binary header
				$font = substr($font, 6);
			}
			if($header && $font[strlen($font)-1]=="\0")
			{
				// Strip final null
				$font = substr($font, 0, -1);
			}
			$this->_put('<<');
			$this->_put('/Length '.strlen($font));
			$this->_put('/Length1 '.$info['length1']);
			$this->_put('/Length2 '.$info['length2']);
			$this->_put('/Filter /ASCIIHexDecode');
			$this->_put('>>');
			$this->_putstream(hex2bin($font));
		}
		else
		{
			$this->_put('<<');
			$this->_put('/Length '.strlen($font));
			if($compressed)
				$this->_put('/Filter /FlateDecode');
			$this->_put('>>');
			$this->_putstream($font);
		}
		$this->_put('endobj');
	}
	for($i=1; $i<=count($this->fonts); $i++)
	{
		$font = $this->fonts[$i];
		$this->_newobj();
		$this->fonts[$i]['n'] = $this->n;
		$this->_put('<<');
		$this->_put('/Type /Font');
		$this->_put('/BaseFont /'.$font['name']);
		$this->_put('/Subtype /Type1');
		$this->_put('/Encoding /WinAnsiEncoding');
		$this->_put('>>');
		$this->_put('endobj');
	}
}

protected function _putimages()
{
	foreach($this->images as $file=>$info)
	{
		$this->_newobj();
		$this->images[$file]['n'] = $this->n;
		$this->_put('<<');
		$this->_put('/Type /XObject');
		$this->_put('/Subtype /Image');
		$this->_put('/Width '.$info['w']);
		$this->_put('/Height '.$info['h']);
		if($info['cs']=='Indexed')
			$this->_put('/ColorSpace [/Indexed /DeviceRGB '.($info['pal']['n']-1).' '.($this->n+1).' 0 R]');
		else
		{
			$this->_put('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK')
				$this->_put('/Decode [1 0 1 0 1 0 1 0]');
		}
		$this->_put('/BitsPerComponent '.$info['bpc']);
		if(isset($info['f']))
			$this->_put('/Filter /'.$info['f']);
		if(isset($info['dp']))
			$this->_put('/DecodeParms <<'.$info['dp'].'>>');
		if(isset($info['trns']) && is_array($info['trns']))
		{
			$trns = '';
			for($i=0; $i<count($info['trns']); $i++)
				$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_put('/Mask ['.$trns.']');
		}
		if(isset($info['smask']))
			$this->_put('/SMask '.($this->n+1).' 0 R');
		$this->_put('/Length '.strlen($info['data']));
		$this->_put('>>');
		$this->_putstream($info['data']);
		$this->_put('endobj');
		// Soft mask
		if(isset($info['smask']))
		{
			$this->_newobj();
			$this->_put('<<');
			$this->_put('/Type /XObject');
			$this->_put('/Subtype /Image');
			$this->_put('/Width '.$info['w']);
			$this->_put('/Height '.$info['h']);
			$this->_put('/ColorSpace /DeviceGray');
			$this->_put('/BitsPerComponent 8');
			$this->_put('/Filter /FlateDecode');
			$this->_put('/Length '.strlen($info['smask']));
			$this->_put('>>');
			$this->_putstream($info['smask']);
			$this->_put('endobj');
		}
		// Palette
		if($info['cs']=='Indexed')
		{
			$this->_newobj();
			$this->_put('<<');
			$this->_put('/Length '.strlen($info['pal']['data']));
			$this->_put('>>');
			$this->_putstream($info['pal']['data']);
			$this->_put('endobj');
		}
	}
}

protected function _putxobjects()
{
	// Do nothing
}

protected function _putannots()
{
	// Do nothing
}

protected function _putlinks()
{
	// Do nothing
}

protected function _putinfo()
{
	$this->_newobj();
	$this->offsets[0] = $this->bufferlen;
	$this->_put('<<');
	$this->_put('/Producer (FPDF '.FPDF::VERSION.')');
	if($this->title)
		$this->_put('/Title '.$this->_textstring($this->title));
	if($this->subject)
		$this->_put('/Subject '.$this->_textstring($this->subject));
	if($this->author)
		$this->_put('/Author '.$this->_textstring($this->author));
	if($this->keywords)
		$this->_put('/Keywords '.$this->_textstring($this->keywords));
	if($this->creator)
		$this->_put('/Creator '.$this->_textstring($this->creator));
	$this->_put('/CreationDate (D:'.date('YmdHis').'+00\04700")');
	$this->_put('>>');
	$this->_put('endobj');
}

protected function _putcatalog()
{
	$this->_newobj();
	$this->_put('<<');
	$this->_put('/Type /Catalog');
	$this->_put('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')
		$this->_put('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth')
		$this->_put('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')
		$this->_put('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))
		$this->_put('/OpenAction [3 0 R /XYZ null null '.sprintf('%.2F', $this->ZoomMode/100).']');
	if($this->LayoutMode=='single')
		$this->_put('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')
		$this->_put('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two')
		$this->_put('/PageLayout /TwoPageLeft');
	$this->_put('>>');
	$this->_put('endobj');
}

protected function _puttrailer()
{
	$this->_put('xref');
	$this->_put('0 '.($this->n+1));
	$this->_put('0000000000 65535 f ');
	for($i=0; $i<=$this->n; $i++)
		$this->_put(sprintf('%010d 00000 n ', $this->offsets[$i]));
	$this->_put('trailer');
	$this->_put('<<');
	$this->_put('/Size '.($this->n+1));
	$this->_put('/Root '.$this->n.' 0 R');
	$this->_put('/Info '.$this->offsets[0].' 0 R');
	$this->_put('>>');
	$this->_put('startxref');
	$this->_put($this->bufferlen);
	$this->_put('%%EOF');
}

protected function _newobj()
{
	// Begin a new object
	$this->n++;
	$this->offsets[$this->n] = $this->bufferlen;
	$this->_put($this->n.' 0 obj');
}

protected function _put($s)
{
	// Write string to the buffer
	$this->buffer .= $s."\n";
	$this->bufferlen += strlen($s)+1;
}

protected function _putstream($s)
{
	$this->_put('stream');
	$this->_put($s);
	$this->_put('endstream');
}

protected function _loadfont($font)
{
	// Load a font definition file from the font directory
	$file = $this->fontpath.$font;
	if(!file_exists($file))
		$this->Error('Font file not found: '.$file);
	$info = require($file);
	if(!is_array($info))
		$this->Error('Incorrect font definition file: '.$file);
	// Check if font file is embedded
	if(isset($info['file']) && !in_array($info['file'], $this->FontFiles))
	{
		$this->FontFiles[$info['file']] = array('length1'=>$info['length1'], 'length2'=>$info['length2']);
	}
	return $info;
}

protected function _dounderline($x, $y, $txt)
{
	// Underline text
	$up = $this->CurrentFont['up'];
	$ut = $this->CurrentFont['ut'];
	$w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt, ' ');
	return sprintf('%.2F %.2F %.2F %.2F re f', $x*$this->k, ($this->h-($y-$up/1000*$this->FontSize))*$this->k, $w*$this->k, -$ut/1000*$this->FontSize*$this->k);
}

protected function _parsejpg($file)
{
	// Extract info from a JPEG file
	$a = getimagesize($file);
	if(!$a)
		$this->Error('Missing or incorrect image file: '.$file);
	if($a[2]!=2)
		$this->Error('Not a JPEG file: '.$file);
	if(!isset($a['channels']) || $a['channels']==3)
		$cs = 'DeviceRGB';
	elseif($a['channels']==4)
		$cs = 'DeviceCMYK';
	else
		$cs = 'DeviceGray';
	$info = array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$cs, 'bpc'=>8, 'f'=>'DCTDecode', 'data'=>file_get_contents($file));
	return $info;
}

protected function _parsepng($file)
{
	// Extract info from a PNG file
	f = fopen($file, 'rb');
	if(!$f)
		$this->Error('Can\'t open image file: '.$file);
	// Check signature
	if($this->_readstream($f, 8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
		$this->Error('Not a PNG file: '.$file);
	// Read header chunk
	$this->_readstream($f, 4);
	if($this->_readstream($f, 4)!='IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	w = $this->_readint($f);
	h = $this->_readint($f);
	bpc = ord($this->_readstream($f, 1));
	if($bpc>8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct = ord($this->_readstream($f, 1));
	if($ct==0 || $ct==4)
		$cs = 'DeviceGray';
	elseif($ct==2 || $ct==6)
		$cs = 'DeviceRGB';
	elseif($ct==3)
		$cs = 'Indexed';
	else
		$this->Error('Unknown color type: '.$file);
	if(ord($this->_readstream($f, 1))!=0)
		$this->Error('Unknown compression method: '.$file);
	if(ord($this->_readstream($f, 1))!=0)
		$this->Error('Unknown filter method: '.$file);
	if(ord($this->_readstream($f, 1))!=0)
		$this->Error('Interlacing not supported: '.$file);
	$this->_readstream($f, 4);
	$dp = '/Predictor 15 /Colors '.($cs=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;
	$trns = '';
	smask = '';
	$pal = '';
	$data = '';
	// Read chunks
	while(!feof($f))
	{
		n = $this->_readint($f);
		type = $this->_readstream($f, 4);
		if($type=='IDAT')
			$data .= $this->_readstream($f, $n);
		elseif($type=='PLTE')
			$pal = $this->_readstream($f, $n);
		elseif($type=='tRNS')
		{
			t = $this->_readstream($f, $n);
			if($ct==0)
				$trns = array(ord(substr($t, 1, 1)));
			elseif($ct==2)
				$trns = array(ord(substr($t, 1, 1)), ord(substr($t, 3, 1)), ord(substr($t, 5, 1)));
			else
			{
				$pos = strpos($t, chr(0));
				if($pos!==false)
					$trns = array($pos);
			}
		}
		elseif($type=='iCCP')
		{
			$this->Error('iCCP chunk not supported: '.$file);
		}
		elseif($type=='tEXt' || $type=='zTXt' || $type=='bKGD' || $type=='hIST' || $type=='pHYs' || $type=='sBIT')
		{
			// Do nothing
		}
		elseif($type=='IEND')
			break;
		else
			$this->Error('Unknown PNG chunk: '.$type);
		$this->_readstream($f, 4);
	}
	fclose($f);
	if($ct==3)
	{
		if($pal=='')
			$this->Error('Missing palette in PNG: '.$file);
		$pal = array('n'=>strlen($pal)/3, 'data'=>$pal);
	}
	// If image has an alpha channel, convert it to a soft mask
	if($ct==4 || $ct==6)
	{
		// Extract alpha channel
		$data = gzuncompress($data);
		$color = '';
		$alpha = '';
		if($ct==4)
		{
			// Gray image
			$len = $w;
			for($i=0; $i<$h; $i++)
			{
				$p = (1+$len)*$i;
				$color .= substr($data, $p, $len);
				$alpha .= substr($data, $p+$len, $len);
			}
		}
		else
		{
			// RGB image
			$len = 3*$w;
			for($i=0; $i<$h; $i++)
			{
				$p = (4+$len)*$i;
				$color .= substr($data, $p, $len);
				$alpha .= substr($data, $p+$len, $len);
			}
		}
		$data = $color;
		$smask = $alpha;
	}
	return array('w'=>$w, 'h'=>$h, 'cs'=>$cs, 'bpc'=>$bpc, 'trns'=>$trns, 'pal'=>$pal, 'smask'=>$smask, 'data'=>$data);
}

protected function _readstream($f, $n)
{
	// Read n bytes from stream
	$s = fread($f, $n);
	if(strlen($s)<$n)
		$this->Error('Unexpected end of stream');
	return $s;
}

protected function _readint($f)
{
	// Read a 32-bit big-endian integer from stream
	$s = $this->_readstream($f, 4);
	return unpack('N', $s);
}

protected function _escape($s)
{
	// Escape special characters in strings
	$s = str_replace('\\', '\\\\', $s); // \ -> \\
	$s = str_replace('(', '\(', $s);
	$s = str_replace(')', '\)', $s);
	$s = str_replace('/', '\/', $s);
	$s = str_replace('%', '\%', $s);
	$s = str_replace(chr(0), ' ', $s);
	return $s;
}

protected function _textstring($s)
{
	// Format a text string
	return '('.$this->_escape($s).')';
}

protected function _convert2utf16($s)
{
    // Convert string to UTF-16BE with BOM
    if ($this->iconv)
        $s = iconv('UTF-8', 'UTF-16BE', $s);
    else
        $s = "\xFE\xFF".mb_convert_encoding($s, 'UTF-16BE', 'UTF-8');
    return '('.$this->_escape($s).')';
}

protected function _UTF8toUTF16($s)
{
    // Convert UTF-8 to UTF-16BE with BOM
    return "\xFE\xFF".mb_convert_encoding($s, 'UTF-16BE', 'UTF-8');
}
}
?>
