<?php
function generateCsvContent($data) {
    $output = fopen('php://temp', 'w+');

    if (!$output) {
        throw new Exception("No se pudo abrir el puntero de archivo temporal para escritura.");
    }

    fputcsv($output, array_keys($data[0]));

    foreach ($data as $row) {
        if (fputcsv($output, $row) === false) {
            throw new Exception("Error al escribir en el puntero de archivo temporal.");
        }
    }
    fclose($output); 
}


function readCsv($csvFileName) {
    $result = array();

    try {
        $file = fopen($csvFileName, 'r');

        if (!$file) {
            throw new Exception();
        }

        $headers = fgetcsv($file);
        $result['headers'] = $headers;

        $data = array();

        while (($row = fgetcsv($file)) !== false) {
            $rowData = array();
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index];
            }
            $data[] = $rowData;
        }
        fclose($file);

        return $data;
    } catch (Exception $e) {
        return false;
    }
}

