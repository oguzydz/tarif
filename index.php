<?php

$GLOBALS['thereis'] = false;
$GLOBALS['success'] = false;



// POST olup olmadığını kontrol ediyor
if ($_POST) {

    // başlangıçta diziyi belirliyor ve post üzerindeki değerleri alıyor
    $tarif = array();
    $topic = $_POST['topic'];
    $url = $_POST['url'];

    // Curl bağlantısıyla url'e istek yapıyor.
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    ]);

    // output değişkeni ile html alınıyor.
    $output = curl_exec($ch);
    curl_close($ch);

    // Regex ile image meta tag'inden image'ın bağlantısı alınıyor.
    preg_match_all('/<meta property="og:image" content="(.*?)" \/>/', $output, $images);
    $tarif['image'] =  $images[1][0];


    // JSON'a eklenecek tarifler için çakışmaması adına tarifin id'si alınıyor.
    preg_match_all('/\<script(.*?)?\>(.|\\n)*?\<\/script\>/i', $output, $id);
    preg_match_all('/"recipe":{"id":(.*?)?,"claps"/', $output, $id[0][5]);
    $tarif['id'] = $id[0][5][1][0];



    // Tarif videolu ise html üzerinde farklı değişiklik yapılıyor.
    preg_match_all('/"hasVideo":"(.*?)?","cat/', $id[0][5], $hasVideo);



    // Tarifin başlığı alınıyor.
    preg_match_all('/<h1 class="posttitle post-title heading-font fn" itemprop="name">(.*?)<\/h1>/', $output, $title);
    $tarif['title'] = $title[1][0];


    // Regex'in kolay yapılması için kod içerisinde <p> tagleri <div> e dönüştürülüyor.
    if ($hasVideo[1][0] === "Evet") {
        $output = str_replace('<div class="entry entry_content">', '<div id="entry_content">', $output);
        $tarif['title'] = str_replace(' (videolu)', '', $tarif['title']);
    } else {
        $output = str_replace('<div class="entry_content tagoninread">', '<div id="entry_content">', $output);
    }

    $output = str_replace('<p>', '<div id="ara_baslik">', $output);
    $output = str_replace('</p>', '</div>', $output);
    $output = str_replace('</li>', '</p></div>', $output);
    $output = str_replace('<li itemprop="ingredients">', '<div id="ara_baslik"><p>', $output);
    $output = str_replace('<div class="entry">', '<div id="entry">', $output);


    // set error level
    $internalErrors = libxml_use_internal_errors(true);

    // malzemeler ve tarifler alınıyor.
    preg_match_all('/<div id="ara_baslik">(.*?)<\/div>/', $output, $content);

    // çekilen item içinde içeriğe uygun özellikler varsa array'e push ediyor.
    $new = array_map(function ($item) {

        if (strstr($item, 'için')) {
            if (strlen($item) < 50) {
                return '<baslik>' . $item;
            } else {
                return null;
            }
        } else if (strstr($item, '<p>')) {
            $item = str_replace('<p>', '', $item);
            $item = str_replace('</p>', '', $item);
            return '<malzeme>' . $item;
        }
    }, $content[1]);

    $new = array_filter($new);
    $new = array_values($new);

    $tarif['malzemeler'] = $new;

    // Hazırlanışları temizleyip array'e push ediyor.

    $output = str_replace('<li>', '<div id="hazirlanisi"><p>', $output);
    preg_match_all('/<div id="hazirlanisi"><p>(.*?)<\/p><\/div>/', $output, $hazirlanislar);

    function isHTML($string)
    {
        return $string != strip_tags($string, '<strong>') ? true : false;
    }


    $hazirlanis = array_map(function ($item) {
        if (!isHTML($item)) {
            return strip_tags($item);
        } else {
            return null;
        }
    }, $hazirlanislar[1]);

    $hazirlanis = array_filter($hazirlanis);
    $hazirlanis = array_values($hazirlanis);


    $tarif['hazirlanis'] = $hazirlanis;

    // dizinde bulunan tariflerin json alıp id kontrolü yaptıktan sonra POST'dan gelen topic'i alıp
    // tekrardan json olarak set ediyor.

    $cache = __DIR__ . '/tarif.json';
    $old_tarif = file_get_contents($cache);


    function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['id'] === $id) {
                return $key;
            }
        }
        return null;
    }



    if ($topic === "bakliyat") {


        $get_tarif = json_decode($old_tarif, true);
        $key = searchForId($tarif['id'], $get_tarif['bakliyat']);

        if ($key === null) {
            array_push($get_tarif['bakliyat'], $tarif);
            $get_tarif =  json_encode($get_tarif);
            file_put_contents($cache,  $get_tarif);
            $GLOBALS['success'] = true;
        } else {
            $GLOBALS['thereis'] = true;
        }
    } else if ($topic === "bebekler") {

        $get_tarif = json_decode($old_tarif, true);
        $key = searchForId($tarif['id'], $get_tarif['bebekler']);

        if ($key === null) {
            array_push($get_tarif['bebekler'], $tarif);
            $get_tarif =  json_encode($get_tarif);
            file_put_contents($cache,  $get_tarif);
            $GLOBALS['success'] = true;
        } else {
            $GLOBALS['thereis'] = true;
        }
    } else if ($topic === "aperatif") {

        $get_tarif = json_decode($old_tarif, true);
        $key = searchForId($tarif['id'], $get_tarif['aperatif']);

        if ($key === null) {
            array_push($get_tarif['aperatif'], $tarif);
            $get_tarif =  json_encode($get_tarif);
            file_put_contents($cache,  $get_tarif);
            $GLOBALS['success'] = true;
        } else {
            $GLOBALS['thereis'] = true;
        }
    } else if ($topic === "sandviç") {

        $get_tarif = json_decode($old_tarif, true);
        $key = searchForId($tarif['id'], $get_tarif['sandviç']);

        if ($key === null) {
            array_push($get_tarif['sandviç'], $tarif);
            $get_tarif =  json_encode($get_tarif);
            file_put_contents($cache,  $get_tarif);
            $GLOBALS['success'] = true;
        } else {
            $GLOBALS['thereis'] = true;
        }
    } else if ($topic === "tatlı") {


        $get_tarif = json_decode($old_tarif, true);
        $key = searchForId($tarif['id'], $get_tarif['tatlı']);

        if ($key === null) {
            array_push($get_tarif['tatlı'], $tarif);
            $get_tarif =  json_encode($get_tarif);
            file_put_contents($cache,  $get_tarif);
            $GLOBALS['success'] = true;
        } else {
            $GLOBALS['thereis'] = true;
        }
    } else if ($topic === "çorba") {


        $get_tarif = json_decode($old_tarif, true);
        $key = searchForId($tarif['id'], $get_tarif['çorba']);

        if ($key === null) {
            array_push($get_tarif['çorba'], $tarif);
            $get_tarif =  json_encode($get_tarif);
            file_put_contents($cache,  $get_tarif);
            $GLOBALS['success'] = true;
        } else {
            $GLOBALS['thereis'] = true;
        }
    }


    libxml_use_internal_errors($internalErrors);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarif Parser</title>
    <!-- CSS only -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>

<body>
    <div class="bg-light p-3 pb-2">
        <p class="text-center h4">Tarif Parser Uygulaması </p>
    </div>

    <div class="p-1 mb-2 bg-info text-white text-center">NefisYemekTarifleri.com'un sitesinde bulunan tarifleri php ile html kodunu temizleyip içeriği alma uygulaması</div>

    <div class="container mt-5 pb-5">
        <?php
        if ($GLOBALS['thereis'] === true) {
        ?>
            <div class="alert alert-danger" role="alert">
                Bu tarif listemde var, başka dene! 🙄
            </div>
        <?php }
        ?>

        <?php
        if ($GLOBALS['success'] === true) {
        ?>
            <div class="alert alert-success" role="alert">
                Oh be şimdi daha çok bilgilendim! 😋😎😂
            </div>
        <?php }
        ?>

        <form action="index.php" method="post" class="p-3 bg-light pb-5 rounded-lg">
            <div class="form-group">
                <label for="exampleFormControlFile1">URL</label>
                <input class="form-control" type="text" placeholder="Bağlantı - https://www.nefisyemektarifleri.com/video/mercimek-koftesi-nasil-yapilir/" name="url">
            </div>
            <div class="form-group">
                <label for="exampleFormControlFile2">Başlık</label>
                <select class="form-control" name="topic">
                    <option>tatlı</option>
                    <option>çorba</option>
                    <option>anayemek</option>
                    <option>aperatif</option>
                    <option>genel</option>
                    <option>bebekler</option>
                    <option>sandviç</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-2 float-right">Gönder</button>
        </form>
    </div>
    <div class="col text-center fixed-bottom">
        <h6>made by
            <a class="btn btn-dark btn-sm " data-toggle="collapse" href="http://oguzydz.me" role="button" aria-expanded="false" aria-controls="collapseExample">
                @oguzydz
            </a>
        </h6>
    </div>
</body>

</html>