<?php

$pathVideo = dirname(__FILE__) . '/videos/';

function getVideo($url, $vimeoId, $md5, $filePath, $count, $trys = 1)
{
    echo '---------------------------------------' . PHP_EOL;
    echo 'TENTANDO CRIAR ARQUIVO PELA ' . ($trys) . 'ª vez (' . $count . ') ' . $filePath . PHP_EOL;

    try {
        if ($trys > 5) {
            echo 'PULANDO O ARQUIVO: ' . $vimeoId . PHP_EOL;
            return true;
        }

        file_put_contents($filePath, file_get_contents($url));

        if (md5_file($filePath) != $md5) {
            throw new \Exception('FALHA NA VERIFICAÇÃO MD5: (' . $count . '): ' . $vimeoId);
        }

        echo 'ARQUIVO CRIADO (' . $count . ') ' . $filePath . PHP_EOL;
        echo '---------------------------------------' . PHP_EOL;

        return true;
    } catch (\Exception $e) {
        echo 'PROBLEMA AO BAIXAR VIDEO: (' . $count . '): ' . $vimeoId . PHP_EOL;
        echo '---------------------------------------' . PHP_EOL;
        echo $e->getMessage() . PHP_EOL;
        echo '---------------------------------------' . PHP_EOL;
        echo 'ESPERANDO 30s para recomeçar...' . PHP_EOL;
        sleep(30);
        $trys++;
        getVideo($url, $vimeoId, $md5, $filePath, $count, $trys);
    }
}

$handle = fopen("./download.txt", "r");
if ($handle) {
    $i = 1;

    while (($line = fgets($handle)) !== false) {

        $array = explode('<===>', $line);
        $url = $array[0];
        $vimeoId = $array[1];
        $md5 = $array[2];
        $size = $array[3];
        $filePath = $pathVideo . $vimeoId . '.mp4';

        getVideo($url, $vimeoId, $md5, $filePath, $i);

        $i++;
    }

    fclose($handle);
} else {
    echo 'Where is ./download.txt ?';
}
