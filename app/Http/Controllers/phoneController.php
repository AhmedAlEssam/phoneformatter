<?php

namespace App\Http\Controllers;

use App\Classes\CountriesList;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class phoneController extends Controller {

  protected $countries;
  public function __construct() {
    $CountriesList = new CountriesList();
    $this->countries = $CountriesList->list();
  }
  
  function index(Request $request ){
    $number = $request->phone? $request->phone: '07700000000';
    dump($this->getAllFormats($request->phone,'IQ'));
    dump($this->knowCountry($request->phone));
    session()->flash('status', 'Done');
    return view('welcome');
  }

  /**
  * getAllFormats() function Will send you all the number format with the country data
  * @param {string} number accept number too and it's should have the phone number
  * @param {string} iso accept string only, it's should have the country code like iq usa etc...
  */
  public function getAllFormats($number, $iso ) {
    $obj =[];
    $tempnum = $this->convertNumbers2English($number);
    $number = Str::of($tempnum)->replaceMatches('/[^0-9]++/', '');
    $obj['isNumber'] =$number == $tempnum;
    $number = $this->normalize($number);
    $country = $this->findCountryByIso($iso);
    $clearNumber = $number;
    $globalK = $country['dial'] . $clearNumber;

    $obj['globalZ'] = "00".$country['dial'] . $clearNumber;
    $obj['globalP'] = "+" . $country['dial'] . $clearNumber;
    $obj['globalK'] = $globalK;
    $obj['domistic'] = "0" . $clearNumber;
    $obj['domistic2'] = ''.$clearNumber;                   
    //concatenating with double "single quotation marks" converts "Stringable" params to string 
    //making it easy to read and use, you can use this method with other "Stringable" params
    $obj['format1'] = $this->format($globalK, "(NNN) NNN-NNNN");
    $obj['format2'] = $this->format($globalK, "NNN.NNN.NNNN");
    $obj['country'] = $country;
    return $obj;
  }
  
  /**
  * knowCountry() function Will try to know what country the number is belong to
  * @param {string} number accept number too and it's should have the phone number
  * @return {array} of countries or {null}
  */
  public function knowCountry($number) {
    $number2 = $this->convertNumbers2English($number);
    $number = ''.Str::of( $number2)->replaceMatches('/[^0-9]++/', '');
    if (($number[0].$number[1] == "00")||($number2[0]=='+')){
      if($number2[0] !='+')
        $number =''.($number >>0 ) ; 
      $str = "";
      $knowCountry=[];
      for ($i = 0; $i < 7; $i++) {
        $str = $str . $number[i];
        if ($this->findCountryByDial($str)) 
          array_push($knowCountry, $this->findCountryByDial($str));
      }
      return $knowCountry;
    }
  }

  /**
  * normalize($Number) Will return a clean $Number without the extra keys
  * @param {string} number accept number too and it's should have the phone number
  */  
  private function normalize($phoneNumber) {
    return Str::of($phoneNumber)->replaceMatches(
      '/^[\+\d{1,3}\-\s]*\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/',
      "$1$2$3"
    );
  }
  
  /**
   * format() Will give you the cool formats for the number like '(964) 781-****'
   * @param {string} number accept number too and it's should have the phone number
   */
  private function format($phoneNumber, $formatString, $options =null) {
    // Normalize the phone number first unless not asked to do so in the options
    if (!$options || !$options['normalize'])
      $phoneNumber = $this->normalize($phoneNumber);
    $l = strlen($phoneNumber) ;
    $phoneNumber = str_split($phoneNumber, 1) ;
    for ($i = 0; $i < $l ; $i++) {
      $formatString = Str::of($formatString)->replaceMatches( '#N#', $phoneNumber[$i] ,1);
    }
    return $formatString;
  }

  /**
   * converts Arabic & Persian numbers to English
   * @param {String} string Any string
   */
  private function convertNumbers2English($string) {
    return 
    strtr($string,
      array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
            '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
            '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
            '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9')
          );
  }
  
  private function findCountryByIso($iso){
    $this->iso = strtoupper($iso);
    return Arr::first($this->countries, function ($value, $key) use ($iso) { return $value['iso2'] == $this->iso; });
  }

  private function findCountryByDial($dial){
    $result = Arr::where($countries , function ($value, $key)use ($dial) { return $value['dial'] == $dial; });
    return $result;
  }
}
