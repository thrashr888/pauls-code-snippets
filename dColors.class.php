<?php

class dColors{

  public static function hex2rgb($hex) {
    $color = str_replace('#','',$hex);
    $rgb = array('r' => hexdec(substr($color,0,2)),
               'g' => hexdec(substr($color,2,2)),
               'b' => hexdec(substr($color,4,2)));
    return $rgb;
  }

  public static function rgb2cmyk($var1,$g=0,$b=0) {
    if(is_array($var1)) {
      $r = $var1['r'];
      $g = $var1['g'];
      $b = $var1['b'];
    }
    else $r=$var1;
    $cyan    = 255 - $r;
    $magenta = 255 - $g;
    $yellow  = 255 - $b;
    $black   = min($cyan, $magenta, $yellow);
    $cyan    = @(($cyan    - $black) / (255 - $black)) * 255;
    $magenta = @(($magenta - $black) / (255 - $black)) * 255;
    $yellow  = @(($yellow  - $black) / (255 - $black)) * 255;
    return array('c' => $cyan / 255,
                'm' => $magenta / 255,
                'y' => $yellow / 255,
                'k' => $black / 255);
  }

  public static function cmyk2rgb($c,$m,$y,$k)
  {

    $red   = $c + $k;
    $green = $m + $k;
    $blue  = $y + $k;

    $red   = ($red - 100) * (-1);
    $green = ($green - 100) * (-1);
    $blue  = ($blue - 100) * (-1);

    $red   = round($red / 100 * 255, 0);
    $green = round($green / 100 * 255, 0);
    $blue  = round($blue / 100 * 255, 0);

    $c = array();
    $c['r'] = $red;
    $c['g'] = $green;
    $c['b'] = $blue;

    return $c;
  }

  public static function hex2cmyk($hex){
    return self::rgb2cmyk(self::hex2rgb($hex));
  }

  public static function convert($color,$return='cmyk'){
    /**
     * $color:
     *   - string: hex
     *   - array(3): rgb
     *   - array(4): cmyk
     * $return:
     *   - array(3): rgb
     *   - array(4): cmyk
     */
    if(is_string($color)){
      // hex
      switch($return){
        case 'cmyk':
          return self::hex2cmyk($color);
          break;
        case 'rgb':
          return self::hex2rgb($color);
          break;
        default:
          return false;
      }
    }elseif(is_array($color) && count($color)==3){
      // rgb
      switch($return){
        case 'cmyk':
          return self::rgb2cmyk($color);
          break;
        case 'rgb':
          return array('r'=>$color[0],'g'=>$color[1],'b'=>$color[2]);
          break;
        default:
          return false;
      }
    }elseif(is_array($color) && count($color)==4){
      // cmyk
      switch($return){
        case 'cmyk':
          return array('c'=>$color[0]/100,'m'=>$color[1]/100,'y'=>$color[2]/100,'k'=>$color[3]/100);
          break;
        case 'rgb':
          return self::cmyk2rgb($color[0],$color[1],$color[2],$color[3]);
          break;
        default:
          return false;
      }
    }else{
      return false;
    }
  }
}