<?php
include('./parser/simple_html_dom.php');
$conn = mysqli_connect("localhost", "u1734578_peternak_id", "u1734578_peternak.id", "u1734578_peternak.id");

$data = run();
$partial = array_chunk($data, 4);

for ($i = 1; $i < count($partial) - 1; $i++) {
  $tdraw_master = array();
  for ($a = 0; $a < count($partial[$i]); $a++) {
    $tdraw[$i][] = $partial[$i][$a];
  }
}
$finaldata = $tdraw;
for ($aa = 1; $aa < count($finaldata) + 1; $aa++) {
  // var_dump($finaldata[$aa]);
  if ($finaldata[$aa][2] !== "-") {
    $cekdata = "SELECT * FROM harga_komoditi where wilayah = '{$finaldata[$aa][1]}'";
    $execdata = mysqli_query($conn, $cekdata);
    if (mysqli_num_rows($execdata) > 0) {
      $query = "UPDATE harga_komoditi set harga = '{$finaldata[$aa][2]}' WHERE wilayah = '{$finaldata[$aa][1]}' AND status = 'AYAM BROILER'";
    } else {
      $query = "INSERT INTO `harga_komoditi` (`id`, `wilayah`, `harga`, `keterangan`,`status`, `created_at`, `updated_at`) VALUES (NULL, '{$finaldata[$aa][1]}', '{$finaldata[$aa][2]}', NULL,'AYAM BROILER', current_timestamp(), '0000-00-00 00:00:00.000000')";
    }
    mysqli_query($conn, $query);
  }
}
echo json_encode(array('status' => true, 'message' => 'Get data success'));
// var_dump($tdraw);
// $output = implode("", $tdraw) . "<br>";
// echo $output;

// $dataarr = array();
// for ($i = 1; $i < count(run()); $i++) {
//   if ($i % 4 == 0) {
//     $savelastindex = $i;
//     $firstlastindex = $i - 4;
//     $index = 0;
//     for ($a = $firstlastindex; $a < $savelastindex; $a++) {
//       switch ($index) {
//         case '0':
//           $dataarr[$no] = array('provinsi' => $data[$a]);
//           break;
//         case '1':
//           $dataarr[$no] = array('kota' => $data[$a]);
//           break;
//         case '2':
//           $dataarr[$no] = array('harga' => $data[$a]);
//           break;
//         case '3':
//           $dataarr[$no] = array('keterangan' => $data[$a]);
//           break;
//       }
//       $index++;
//       // echo $data[$a] . "<br>";
//     }
//     $no++;
//     // echo $i . "<br>";
//     echo json_encode($dataarr);
//   }
// }
function logger($data, $result)
{
  $file = './log/log.txt';
  // Open the file to get existing content
  $current = file_get_contents($file);
  // Append a new person to the file
  $current .= "Kode : $data => $result \n";
  // Write the contents back to the file
  file_put_contents($file, $current);
}
function run()
{
  $curl = curl_init();

  curl_setopt_array($curl, array(
    // CURLOPT_URL => "https://ews.kemendag.go.id/Rdesign_News.aspx?v=8834",
    CURLOPT_URL => "https://www.komoditasternak.com/daftar-harga-ayam-broiler-hari-ini/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  // echo $response;
  $html = str_get_html($response);
  // foreach ($html->find('table[@id="tablepress-2"]') as $e) {
  //   var_dump($e);
  //   // foreach ($e->find('td.text') as $cell) {

  //   //   // push the cell's text to the array
  //   //   echo  $cell->innertext;
  //   // }
  // }
  foreach ($html->find('table tr td') as $e) {
    $arr[] = trim($e->innertext);
  }

  return $arr;
  // $raw = implode(" ", $datanya);

  // var_dump($html->find('table[@id="tablepress-2"]'));
  // return $html->find('div.tablepress-2'); // <div class="notice">RADIUS server tidak merespon</div>
}

function generateRandomString($length1)
{
  $length = $length1 - 2;
  $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
  $charactersLength = strlen($characters);
  $randomString = '5k';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}
function show_status($datanya, $done, $total, $size = 30, $output)
{
  // static $start_time;
  $start_time = strtotime(date('Y-m-d H:i:s'));
  static $start_time;
  // if we go over our bound, just ignore it
  if ($done > $total) return;

  if (empty($start_time)) $start_time = time();
  $now = time();

  $perc = (float)($done / $total);

  $bar = floor($perc * $size);

  $status_bar = "\r[";
  $status_bar .= str_repeat("=", $bar);
  if ($bar < $size) {
    $status_bar .= ">";
    $status_bar .= str_repeat(" ", $size - $bar);
  } else {
    $status_bar .= "=";
  }
  $disp = number_format($perc * 100, 0);

  $status_bar .= "] $disp%  $done/$total";
  if ($done == 0) {
    $rate = ($now - $start_time);
  } else {
    $rate = ($now - $start_time) / $done;
  }
  $left = $total - $done;
  $eta = round($rate * $left, 2);

  $elapsed = $now - $start_time;

  $status_bar .= " try : $datanya output : {$output} remaining: " . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

  echo "$status_bar  ";

  flush();

  // when done, send a newline
  if ($done == $total) {
    echo "\n";
  }
}
