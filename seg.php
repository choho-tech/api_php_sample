<?php

$BASE_URL = "<your base_url>";
$FILE_SERVER_URL = "<your file_server_url>";
$USER_ID = "<your user_id>";
$ZH_TOKEN = "<your zh_token>";

$FILE_PATH = "l.stl"; // local STL file path
$JAW_TYPE = "Lower"; // Upper for upper jaw and Lower for lower jaw

// Step 1. upload stl to file server
$t = time();
$curl = curl_init($FILE_SERVER_URL.
"/scratch/APIClient/". $USER_ID. "/upload_url?postfix=stl");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "X-ZH-TOKEN: ".$ZH_TOKEN,
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$resp = curl_exec($curl);
curl_close($curl);
$stlURL = json_decode($resp);
$uid_loc = strpos($stlURL, $USER_ID) + strlen($USER_ID) + 1;
$stlURN = substr($stlURL, $uid_loc, strpos($stlURL, "?") - $uid_loc);
$stlURN = "urn:zhfile:o:s:APIClient:". $USER_ID. ":". $stlURN;

$curl = curl_init($stlURL);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_PUT, 1);
curl_setopt($curl, CURLOPT_UPLOAD, true);

$stlFile = fopen($FILE_PATH, "rb");
curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
curl_setopt($curl, CURLOPT_INFILE, $stlFile);
curl_setopt($curl, CURLOPT_INFILESIZE, filesize($FILE_PATH));

$resp = curl_exec($curl);
curl_close($curl);

echo "upload takes ".(string)(time()-$t)." seconds\n";

// Step 2. launch job
$jobReq = [
    "spec_group" => "mesh-processing",
    "spec_name" => "oral-seg",
    "spec_version" => "1.0-snapshot",
    "user_id" => $USER_ID,
    "user_group" => "APIClient",
    "input_data" => [
        "jaw_type" => $JAW_TYPE,
        "mesh" => [
            "type" => "stl",
            "data" => $stlURN
        ]
    ],
    "output_config" => [
        "mesh" => [
            "type" => "stl"
        ]
    ]
];

$curl = curl_init($BASE_URL. "/run");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "X-ZH-TOKEN: ".$ZH_TOKEN,
    "accept: application/json",
    "Content-Type: application/json"
);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($jobReq));

$resp = curl_exec($curl);
curl_close($curl);
$run_id = get_object_vars(json_decode($resp))["run_id"];

echo "run_id is: ".$run_id."\n";

$t=time();

// Step 3. wait until job finished
while(true){
    sleep(3);
    $curl = curl_init($BASE_URL. "/run/". $run_id);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "X-ZH-TOKEN: ".$ZH_TOKEN,
        "accept: application/json"
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $resp = curl_exec($curl);
    curl_close($curl);
    $resp = get_object_vars(json_decode($resp));
    if($resp["failed"]) die("job failed with reason: ".$resp["reason_public"]);
    if($resp["completed"]) break;
}

echo "Job completed in ".(string)(time()-$t)." seconds\n";

// Step 4. Get results
$curl = curl_init($BASE_URL. "/data/". $run_id);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "X-ZH-TOKEN: ".$ZH_TOKEN,
    "accept: application/json"
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$resp = curl_exec($curl);
curl_close($curl);

$resp = get_object_vars(json_decode($resp));

$labelfile = fopen("seg_labels.txt", "w") or die("Unable to write file!");
foreach ($resp["seg_labels"] as $value) {
    fwrite($labelfile, strval($value)."\n");
}
fclose($labelfile);


$mesh = fopen("processed_mesh.stl", "w") or die("Unable to write file!");

// Step 5. download mesh file from file server.
$curl = curl_init($FILE_SERVER_URL. "/file/download?urn=". get_object_vars($resp["mesh"])["data"]);

curl_setopt($curl, CURLOPT_FILE, $mesh);
curl_setopt($curl, CURLOPT_TIMEOUT, 60);
$headers = array(
    "X-ZH-TOKEN: ".$ZH_TOKEN
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_exec($curl);
curl_close($curl);
fclose($mesh);

echo "Completed: Mesh saved to processed_mesh.stl and Label saved to seg_labels.txt\n";