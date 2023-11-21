<?php
function generateCsv($csvFileName, $data){
    try {
        $file = fopen($csvFileName, 'w');
    
        if (!$file) {
            throw new Exception("No se pudo abrir el archivo $csvFileName para escritura.");
        }

        fputcsv($file, array_keys($data[0]));
    
        foreach ($data as $row) {
            if (fputcsv($file, $row) === false) {
                throw new Exception("Error al escribir en el archivo $csvFileName.");
            }
        }
    
        fclose($file);
        return "Datos guardados en $csvFileName.";
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
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

