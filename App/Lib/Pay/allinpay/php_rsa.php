<?php   
/*  
* Implementation of the RSA algorithm  
* (C) Copyright 2004 Edsko de Vries, Ireland  
*  
* Licensed under the GNU Public License (GPL)  
*  
* This implementation has been verified against [3]   
* (tested Java/PHP interoperability).  
*  
* References:  
* [1] "Applied Cryptography", Bruce Schneier, John Wiley & Sons, 1996  
* [2] "Prime Number Hide-and-Seek", Brian Raiter, Muppetlabs (online)  
* [3] "The Bouncy Castle Crypto Package", Legion of the Bouncy Castle,  
* (open source cryptography library for Java, online)  
* [4] "PKCS #1: RSA Encryption Standard", RSA Laboratories Technical Note,  
* version 1.5, revised November 1, 1993  
*/  
  
  
/*  
* Functions that are meant to be used by the user of this PHP module.  
*  
* Notes:  
* - $key and $modulus should be numbers in (decimal) string format  
* - $message is expected to be binary data  
* - $keylength should be a multiple of 8, and should be in bits  
* - For rsa_encrypt/rsa_sign, the length of $message should not exceed   
* ($keylength / 8) - 11 (as mandated by [4]).  
* - rsa_encrypt and rsa_sign will automatically add padding to the message.   
* For rsa_encrypt, this padding will consist of random values; for rsa_sign,  
* padding will consist of the appropriate number of 0xFF values (see [4])  
* - rsa_decrypt and rsa_verify will automatically remove message padding.  
* - Blocks for decoding (rsa_decrypt, rsa_verify) should be exactly   
* ($keylength / 8) bytes long.  
* - rsa_encrypt and rsa_verify expect a public key; rsa_decrypt and rsa_sign  
* expect a private key.  
*/  
 
/*
 * johnwinner  modify list
 *  1)suport digest method in rsa_sign and rsa_sign (sha1 or md5 )
 *  2)rsa_sign return signature in base64 format
 *  3)modify rsa_verify define 
 *    support source documnet and base64 signatire input, boolean output 
 *  
 * all above base on Edsko de Vries' work
 * 
 */

/**
 * 
 * @param $message     // $message is expected to be binary data 
 * @param $public_key  // $modulus should be numbers in (decimal) string format
 * @param $modulus     // $modulus should be numbers in (decimal) string format
 * @param $keylength   // int
 * @return  $result     // result is binary data 
 */
function rsa_encrypt($message, $public_key, $modulus, $keylength)   
{   
    $padded = add_PKCS1_padding($message, true, $keylength / 8);   
    $number = binary_to_number($padded);   
    $encrypted = pow_mod($number, $public_key, $modulus);   
    $result = number_to_binary($encrypted, $keylength / 8);   
    return $result;   
  
}   

/**
 * 
 * @param $message     // $message is expected to be binary data 
 * @param $private_key // $modulus should be numbers in (decimal) string format
 * @param $modulus     // $modulus should be numbers in (decimal) string format
 * @param $keylength   // int
 */
function rsa_decrypt($message, $private_key, $modulus, $keylength)   
{   
    $number = binary_to_number($message);   
    $decrypted = pow_mod($number, $private_key, $modulus);   
    $result = number_to_binary($decrypted, $keylength / 8);   
    return remove_PKCS1_padding($result, $keylength / 8);   
}   

/**
 * 
 * @param $message     // $message is expected to be binary data 
 * @param $private_key // $modulus should be numbers in (decimal) string format
 * @param $modulus     // $modulus should be numbers in (decimal) string format
 * @param $keylength   // int
 * @param $hash_func   // name of hash function, which will be used during signing
 * @return $result     // signature String in Base64 format
 */
function rsa_sign($message, $private_key, $modulus, $keylength,$hash_func)   
{   
	//only suport sha1 or md5 digest now
	if (!function_exists($hash_func) && (strcmp($hash_func ,'sha1') == 0 || strcmp($hash_func,'md5') == 0))
		return false;
	$mssage_digest_info_hex = $hash_func($message);
	$mssage_digest_info_bin = hex2bin($mssage_digest_info_hex);
    $padded = add_PKCS1_padding($mssage_digest_info_bin, false, $keylength / 8);
    $number = binary_to_number($padded);   
    $signed = pow_mod($number, $private_key, $modulus);   
    $result = base64_encode($signed); 
    return $result;   
}   

/**
 * 
 * @param $message     // $message is expected to be binary data 
 * @param $private_key // $modulus should be numbers in (decimal) string format
 * @param $modulus     // $modulus should be numbers in (decimal) string format
 * @param $keylength   // int
 * @param $hash_func   // name of hash function, which will be used during signing
 * @return boolean     // true or false
 */
function rsa_verify($document, $signature, $public_key, $modulus, $keylength,$hash_func)   
{   
	//only suport sha1 or md5 digest now
	if (!function_exists($hash_func) && (strcmp($hash_func ,'sha1') == 0 || strcmp($hash_func,'md5') == 0))
		return false;
	$document_digest_info = $hash_func($document);
	
	$number    = binary_to_number(base64_decode($signature));   
    $decrypted = pow_mod($number, $public_key, $modulus);   
    $decrypted_bytes    = number_to_binary($decrypted, $keylength / 8);   
    if($hash_func == "sha1" )
    {
    	$result = remove_PKCS1_padding_sha1($decrypted_bytes, $keylength / 8);
    }
    else
    {
    	$result = remove_PKCS1_padding_md5($decrypted_bytes, $keylength / 8);
    }
	return(hex2bin($document_digest_info) == $result);
}   
  
  
/*  
* Some constants  
*/  
  
  
define("BCCOMP_LARGER", 1);   
  
  
/*  
* The actual implementation.  
* Requires BCMath support in PHP (compile with --enable-bcmath)  
*/  
  
  
//-- 
// Calculate (p ^ q) mod r    
//   
// We need some trickery to [2]:   
// (a) Avoid calculating (p ^ q) before (p ^ q) mod r, because for typical RSA   
// applications, (p ^ q) is going to be _WAY_ too large.   
// (I mean, __WAY__ too large - won't fit in your computer's memory.)   
// (b) Still be reasonably efficient.   
//   
// We assume p, q and r are all positive, and that r is non-zero.   
//   
// Note that the more simple algorithm of multiplying $p by itself $q times, and   
// applying "mod $r" at every step is also valid, but is O($q), whereas this   
// algorithm is O(log $q). Big difference.   
//   
// As far as I can see, the algorithm I use is optimal; there is no redundancy   
// in the calculation of the partial results.    
//--   
  
function pow_mod($p, $q, $r)   
{   
    // Extract powers of 2 from $q   
    $factors = array();   
    $div = $q;   
    $power_of_two = 0;   
    while(bccomp($div, "0") == BCCOMP_LARGER)   
    {   
        $rem = bcmod($div, 2);   
        $div = bcdiv($div, 2);   
  
        if($rem) array_push($factors, $power_of_two);   
        $power_of_two++;   
    }   
  
    // Calculate partial results for each factor, using each partial result as a   
    // starting point for the next. This depends of the factors of two being   
    // generated in increasing order.   
  
    $partial_results = array();   
    $part_res = $p;   
    $idx = 0;   
    
    foreach($factors as $factor)   
    {   
        while($idx < $factor)   
        {   
            $part_res = bcpow($part_res, "2");   
            $part_res = bcmod($part_res, $r);   
            $idx++;   
        }   
        array_push($partial_results, $part_res);   
    }   

    // Calculate final result   
    $result = "1";   
    foreach($partial_results as $part_res)   
    {   
        $result = bcmul($result, $part_res);   
        $result = bcmod($result, $r);   
    }   
    return $result;   
}   
  
//--   
// Function to add padding to a decrypted string   
// We need to know if this is a private or a public key operation [4]   
//--   
  
function add_PKCS1_padding($data, $isPublicKey, $blocksize)   
{   
    $pad_length = $blocksize - 3 - strlen($data);   
    if($isPublicKey)   
    {   
        $block_type = "\x02";   
        $padding = "";   
        for($i = 0; $i < $pad_length; $i++)   
        {   
            $rnd = mt_rand(1, 255);   
            $padding .= chr($rnd);   
        }   
    }   
    else  
    {   
        $block_type = "\x01";   
        $padding = str_repeat("\xFF", $pad_length);   
    }   
  
    return "\x00" . $block_type . $padding . "\x00" . $data;   
}  
 
//--   
// Remove padding from a decrypted string   
// See [4] for more details.   
//--   
  
function remove_PKCS1_padding($data, $blocksize)   
{   
    //assert(strlen($data) == $blocksize);   
    $data = substr($data, 1);   
 
    // We cannot deal with block type 0   
    if($data{0} == '\0')   
        die("Block type 0 not implemented.");   
  
    // Then the block type must be 1 or 2    
    //assert(($data{0} == "\x01") || ($data{0} == "\x02"));   
  
    // Remove the padding   
    $offset = strpos($data, "\0", 1);   
    return substr($data, $offset + 1);   
}   

function remove_PKCS1_padding_sha1($data, $blocksize) {
	$digestinfo = remove_PKCS1_padding($data, $blocksize);
	$digestinfo_length = strlen($digestinfo);
	//sha1 digestinfo length not less than 20
	//assert($digestinfo_length >= 20);

	return substr($digestinfo, $digestinfo_length-20);
}   

function remove_PKCS1_padding_md5($data, $blocksize) {
	$digestinfo = remove_PKCS1_padding($data, $blocksize);
	$digestinfo_length = strlen($digestinfo);
	//md5 digestinfo length not less than 16
	//assert($digestinfo_length >= 16);

	return substr($digestinfo, $digestinfo_length-16);
}
  
//--   
// Convert binary data to a decimal number   
//--   
  
function binary_to_number($data)   
{   
    $base = "256";   
    $radix = "1";   
    $result = "0";   
  
    for($i = strlen($data) - 1; $i >= 0; $i--)   
    {   
        $digit = ord($data{$i});   
        $part_res = bcmul($digit, $radix);   
        $result = bcadd($result, $part_res);   
        $radix = bcmul($radix, $base);   
    }   
    return $result;   
}   
  
//--   
// Convert a number back into binary form   
//--   
function number_to_binary($number, $blocksize)   
{   
    $base = "256";   
    $result = "";   
    $div = $number;   
    while($div > 0)   
    {   
        $mod = bcmod($div, $base);   
        $div = bcdiv($div, $base);   
        $result = chr($mod) . $result;   
    }   
    return str_pad($result, $blocksize, "\x00", STR_PAD_LEFT);   
}   
//
//Convert hexadecimal format data into  binary 
//
function hex2bin($data) {
  $len = strlen($data);
  $newdata='';
  for($i=0;$i<$len;$i+=2) {
      $newdata .= pack("C",hexdec(substr($data,$i,2)));
  }
  return $newdata;
}

?>   