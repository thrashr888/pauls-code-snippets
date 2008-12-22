<?php

class dPDF{

	protected static $font_list = array(
        'Helvetica',
        'Helvetica-Bold',
        'Helvetica-Oblique',
        'Helvetica-BoldOblique',
        'Courier',
        'Courier-Bold',
        'Courier-Oblique',
        'Courier-BoldOblique',
        'Times-Roman',
        'Times-Bold',
        'Times-Italic',
        'Times-BoldItalic',
        'Symbol',
        'ZapfDingbats',
	);

	protected static $default_color = "#000000";

	protected static $encoding = "winansi";

	public static function log($message){
		sfContext::getInstance()->getLogger()->log($message);
	}

	public static function buildPDF($width=612, $height=792, Array $pdf_text=null, Array $pdf_image=null, Array $pdf_shape=null, Array $pdf_pdf=null)
	{
		// Note about font sizes: they are pixels!
		// to really get accurate centering measure each letter and save it's pixel width in a hash

		$poster = PDF_new();

		# This means we must check return values of load_font() etc.
		PDF_set_parameter($poster, "errorpolicy", "return");
		/* This line is required to avoid problems on Japanese systems */
		PDF_set_parameter($poster, "hypertextencoding", self::$encoding);

		PDF_set_parameter($poster, "compatibility", '1.5'); // acrobat version 6

		/*  open new PDF file; insert a file name to create the PDF on disk */
		if (PDF_begin_document($poster, "", "") == 0) {
			die("Error: ".PDF_get_errmsg($poster));
		}

		// setup the metadata
		PDF_set_info($poster, "Creator", sfConfig::get('app_pdf_creator'));
		PDF_set_info($poster, "Author", sfConfig::get('app_pdf_author'));
		PDF_set_info($poster, "Title", sfConfig::get('app_pdf_title'));
		PDF_set_info($poster, "Subject", sfConfig::get('app_pdf_title'));
		PDF_begin_page_ext($poster, $width, $height, ""); // w x h : 8.5 X 11

		if($pdf_shape!=null){
			// make all the shapes
			foreach($pdf_shape as $shape){
				self::addShape($poster,$shape);
				unset($shape);
			}
		}

		// print all the images
		if($pdf_image!=null){
			foreach($pdf_image as $image){
				self::addImage($poster,$image,$width);
				unset($image);
			}
		}

		if($pdf_text!=null){
			// wrap all of the multiline text elements
			foreach($pdf_text as $key=>$text){
				if(isset($text['wordwrap']) && $text['wordwrap']>0){
					// this wraps a line
					$text['text'] = wordwrap($text['text'], $text['wordwrap'], 'OHNOES', true);
					$text['text'] = explode('OHNOES', $text['text']);
					foreach($text['text'] as $text_row) {
						$pdf_text[] = array(
			     'element'  => 'text',
			     'text'     => $text_row,
			     'size'     => $text['size'],
			     'x'        => $text['x'],
			     'y'        => $text['y'],
			     'bold'     => $text['bold'],
			     'italic'   => $text['italic'],
			     'font'     => $text['font'],
			     'center'   => $text['center'],
						);
						$text['y'] -= $text['linespacing'];
					}
					unset($pdf_text[$key]);
				}
			}

			// print all the text lines
			foreach($pdf_text as $text){
				self::addText($poster,$text,$width,self::$encoding);
				unset($text);
			}
		}

		if($pdf_pdf!=null){
			foreach($pdf_pdf as $pdf){
				self::addPDF($poster,$pdf);
				unset($pdf);
			}
		}

		// FINISH UP
		PDF_end_page_ext($poster, "");
		PDF_end_document($poster, "");
		$buf = PDF_get_buffer($poster);
		PDF_delete($poster);

		return $buf;
	}

	public static function addShape($poster,$shape){
		// prints a circle
		$color = dColors::convert(array_key_exists('color',$shape)?$shape['color']:self::$default_color); // default: black
		PDF_setcolor($poster, "fillstroke", "cmyk", $color['c'], $color['m'], $color['y'], $color['k']);

		if($shape['type']=='circle'){
			PDF_circle($poster, $shape['x'], $shape['y'], $shape['radius']);
		}elseif($shape['type']=='rect'){
			PDF_rect($poster, $shape['x'], $shape['y'], $shape['width'], $shape['height']);
		}

		PDF_fill($poster);
		PDF_save($poster);
		PDF_restore($poster);
	}

	public static function addImage($poster,$image,$width=false){
		try {
			$imageHolder = PDF_load_image($poster, $image['img_type'], $image['src'], "");
			if (!$imageHolder) {
				throw new Exception('cannot load image from ' . $image['src']);
			}
		} catch (Exception $e) {
			dUtils::logException($e);
		}

		$image['x'] = $image['center'] ? ($width/2 - $image['width']/2) : $image['x'];

		if($image['fit'] && eregi('meet|clip|slice',$image['fit'])){
			PDF_fit_image($poster, $imageHolder, $image['x'], $image['y'], 'boxsize {'.$image['width'].' '.$image['height'].'} fitmethod '.$image['fit']);
		}else{
			PDF_place_image($poster, $imageHolder, $image['x'], $image['y'], 1);
		}
		PDF_close_image($poster, $imageHolder);
	}

	public static function addText($poster,$text,$width,$encoding=false){
		// pick a font:
		$text['italic'] = isset($text['italic']) ? $text['italic'] : null;
		$text['bold'] = isset($text['bold']) ? $text['bold'] : null;
		$text['font'] = isset($text['font']) ? $text['font'] : null;

		$use_font = self::chooseFont($poster,$text['font'],$text['italic'],$text['bold'],$encoding);
		PDF_setfont($poster, $use_font, $text['size']);

		// center it?
		if(isset($text['center-box'])){
			$text['x'] = $text['x'] + $text['center-box']/2 - PDF_stringwidth($poster, $text['text'], $use_font, $text['size'])/2;
		}else{
			$text['x'] = $text['center'] ? self::centerText($width, $text['size'], $text['text']) : $text['x'];
		}
		PDF_set_text_pos($poster, $text['x'], $text['y']); // x, y, starts at bottom left

		//self::log('addTextColor-'.debug($text['color'],0,1));

		$color = dColors::convert(array_key_exists('color',$text)?$text['color']:self::$default_color); // default: black

		//self::log('addTextColor-'.$color['c'].'-'.$color['m'].'-'.$color['y'].'-'.$color['k']);

		PDF_setcolor($poster, "fillstroke", "cmyk", $color['c'], $color['m'], $color['y'], $color['k']);

		// prints the line
		PDF_show($poster, $text['text']);
		PDF_save($poster);
		PDF_restore($poster);
	}

	public static function addPDF($poster,$pdf){
		$pdiHolder = PDF_open_pdi($poster, $pdf['src'], "", 0);
		$pdiPageHolder = PDF_open_pdi_page($pdiHolder, $pdiHolder, 1, "");

		//$pdi_width = pdf_get_pdi_value($poster, "width", $pdiHolder, $pdiPageHolder, 0);
		//$pdi_height = pdf_get_pdi_value($poster, "height", $pdiHolder, $pdiPageHolder, 0);

		PDF_fit_pdi_page($pdiHolder, $pdiPageHolder, $pdf['x'], $pdf['y'], "");
		PDF_close_pdi_page($poster, $pdiPageHolder);

		PDF_save($poster);
		PDF_restore($poster);
	}

	public static function chooseFont($poster, $set_font=false, $is_italic=0, $is_bold=0, $encoding="winansi", $optlist=""){
		if(isset($set_font))
		{
			$use_font = PDF_load_font($poster, $set_font, $encoding, "");
		}
		elseif(isset($is_italic) || isset($is_bold))
		{
			$font_int = $is_bold*10 + $is_italic*1;
			switch($is_bold*10 + $is_italic*1){
				case 11:
					$use_font = PDF_load_font($poster, "Helvetica-BoldOblique", $encoding, $optlist);
				case 10:
					$use_font = PDF_load_font($poster, "Helvetica-Bold", $encoding, $optlist);
				case 01:
					$use_font = PDF_load_font($poster, "Helvetica-Oblique", $encoding, $optlist);
				default:
					$use_font = PDF_load_font($poster, "Helvetica", $encoding, $optlist);
			}
		}else{
			$use_font = PDF_load_font($poster, "Helvetica", $encoding, $optlist);
		}
		return $use_font;
	}

	public static function getFonts($poster,$return_font=false,$encoding = "winansi",$optlist=""){
		$default_font_list = self::$font_list;

		foreach($font_list as $font){
			$font_list[$font] = PDF_load_font($poster, $font, $encoding, $optlist);
		}

		/*autosubsetting boolean (Default: global parameter)
		 autocidfont boolean (Default: global parameter)
		 embedding boolean (Default: false)
		 fontstyle keyword (Possible keywords are normal, bold, italic, bolditalic. Default: normal)
		 fontwarning boolean (true - exception,  false - error code Default: global parameter)
		 kerning boolean (Default: false)
		 monospace integer (Default: absent (metrics from the font will be used))
		 subsetlimit float (Default: global parameter)
		 subsetminsize float (Default: global parameter)
		 subsetting boolean (Default:false)
		 unicodemap boolean (Default: true)*/

		if ($font_list['Helvetica'] == 0) {
			die("Error: ".PDF_get_errmsg($poster));
		}
		return $font_list;
	}

	public static function centerText($width, $fontsize, $text) {
		$text_size = strlen($text) * $fontsize / 2;
		$pos = ($width - $text_size) / 2;
		$pos = (int) round($pos);
		return $pos;
	}

	public static function buildRegistrationMarks($x1,$y1,$x2,$y2,$box_width=28,$spacing=1,$color='#ED1C24',$stroke=1){
		$pdf_shape = array();
		$box_height = $box_width;

		$cropmark_width = $spacing+0.1339;
		$cropmark_length = $box_width+$cropmark_width;
		$cropmark_color = '#000000';

		for($c=1;$c<=4;$c++){
			if($c==1){
				// top/left
				$x_start = $x1;
				$y_start = $y1;
			}elseif($c==2){
				// top/right
				$x_start = $x2;
				$y_start = $y1;
			}elseif($c==3){
				// bottom/right
				$x_start = $x2;
				$y_start = $y2;
			}elseif($c==4){
				// bottom/left
				$x_start = $x1;
				$y_start = $y2;
			}else{
				continue;
			}

			$left_x = $x_start-$box_width-$spacing/2;
			$right_x = $x_start+$spacing/2;
			$top_y = $y_start+$spacing/2;
			$bottom_y = $y_start-$box_height-$spacing/2;

			/**
			 * Boxes
			 */
			if($c!=1){
				// left/top
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'left/top_b'.$c,
	       'width' => $box_width,
	       'height' => $box_height,
	       'x' => $left_x,
	       'y' => $top_y,
	       'stroke' => $stroke,
	       'color' => $color,
				);
			}
			if($c!=2){
				// right/top
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'right/top_b'.$c,
	       'width' => $box_width,
	       'height' => $box_height,
	       'x' => $right_x,
	       'y' => $top_y,
	       'stroke' => $stroke,
	       'color' => $color,
				);
			}
			if($c!=3){
				// right/bottom
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'right/bottom_b'.$c,
	       'width' => $box_width,
	       'height' => $box_height,
	       'x' => $right_x,
	       'y' => $bottom_y,
	       'stroke' => $stroke,
	       'color' => $color,
				);
			}
			if($c!=4){
				// left/bottom
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'left/bottom_b'.$c,
	       'width' => $box_width,
	       'height' => $box_height,
	       'x' => $left_x,
	       'y' => $bottom_y,
	       'stroke' => $stroke,
	       'color' => $color,
				);
			}

			/**
			 * Cropmarks
			 */
			if($c!=2 && $c!=3){
				// right
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'right'.$c,
	       'width' => $cropmark_length,
	       'height' => $cropmark_width,
	       'x' => $x_start-$cropmark_width/2,
	       'y' => $y_start-$cropmark_width/2,
	       'color' => $cropmark_color,
				);
			}
			if($c!=4 && $c!=1){
				// left
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'left'.$c,
	       'width' => $cropmark_length,
	       'height' => $cropmark_width,
	       'x' => $x_start-$cropmark_length+$cropmark_width/2,
	       'y' => $y_start-$cropmark_width/2,
	       'color' => $cropmark_color,
				);
			}
			if($c!=3 && $c!=4){
				// bottom
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'top'.$c,
	       'width' => $cropmark_width,
	       'height' => $cropmark_length,
	       'x' => $x_start-$cropmark_width/2,
	       'y' => $y_start-$cropmark_length+$cropmark_width/2,
	       'color' => $cropmark_color,
				);
			}
			if($c!=2 && $c!=1){
				// top
				$pdf_shape[] = array(
	       'element' => 'shape',
	       'type' => 'rect',
	       'desc' => 'top'.$c,
	       'width' => $cropmark_width,
	       'height' => $cropmark_length,
	       'x' => $x_start-$cropmark_width/2,
	       'y' => $y_start-$cropmark_width/2,
	       'color' => $cropmark_color,
				);
			}
		}
		return $pdf_shape;
	}

}