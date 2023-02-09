<?php

/**
 * Client to create a new Magento 2 package release through the Magento Developer API
 * For more details go to: https://developer.adobe.com/commerce/marketplace/guides/eqp/v1/rest-api/
 */
class MagentoReleaseClient
{
    const API_PATH = 'https://developer-api.magento.com/rest/v1';
    private $version;
    private $release_notes;
    private $ust;
    private $app_id;
    private $app_secret;

    /**
     * Create a MagentoReleaseClient
     *
     * @param string $version Released package version
     * @param string $release_notes Release Notes
     * @param string $app_id Adobe App Id to connect to the EQP API
     * @param string $app_secret Adobe Secret to connect to the EQP API
     */
    public function __construct($version, $release_notes, $app_id, $app_secret)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->version = $version;
        $this->release_notes = $release_notes;

        $this->obtain_ust();
    }

    /**
     * Executes a POST curl request to obtain the User Session Token and stores
     * the received value into the ust property
     *
     * @return void
     */
    private function obtain_ust()
    {
        echo "Obtaining UST\n";
        $auth = base64_encode("$this->app_id:$this->app_secret");
        $headers = [
            'Content-Type: application/json',
            "Authorization: Basic $auth"
        ];

        $payload = [
            "grant_type" => "session"
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_PATH . '/app/session/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 100,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLINFO_HEADER_OUT => true,
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        $response = json_decode($response);
        if (property_exists($response, 'ust')) {
            $this->ust = $response->ust;
            echo " --- Obatined UST: $this->ust\n";
        } else {
            die("Error obtaining the User Session Token. Check credentials");
        }
    }

    /**
     * Executes a POST curl request to upload the needed files to create the
     * release.
     *
     * The create release endpoint needs two files, the '.zip' file containing
     * the code and a documentation file, that in our case is a '.pdf' file
     *
     * We return the list of file identifiers returned by the API. he first
     * value is the zip file id, and the second value is the PDF document ID
     *
     * @return array The list of file identifiers.
     */
    private function upload_files()
    {

        $url = "/files/uploads/";
        $ch = curl_init();
        $headers   = array();
        $headers[] = "Authorization: Bearer " . $this->ust;
        $path = realpath(dirname(__FILE__) . "/../../../");
        $post = [
            'file[0]' => new \CURLFile("$path/doofinder-magento2.zip", 'application/zip', 'doofinder-magento2.zip'),
            'file[1]' => new \CURLFile("$path/Manual.pdf", 'application/pdf', 'Manual.pdf')
        ];

        echo "Uploading files: \n";
        foreach ($post as $file) {
            echo " --- Upload " . $file->postname . "\n";
        }

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_URL            => self::API_PATH . $url,
                CURLOPT_POSTFIELDS     => $post,
                CURLINFO_HEADER_OUT     => true
            ]
        );

        $result = curl_exec($ch);
        curl_close($ch);

        $uploaded_files = json_decode($result);
        $result_files = [];

        foreach ($uploaded_files as $file) {
            $result_files[] = $file->file_upload_id;
        }
        return $result_files;
    }

    /**
     * Executes a POST curl request to submit the new release into the Magento
     * Marketplace.
     *
     * @return void
     */
    public function create_release()
    {
        $result = [];
        $url = "/products/packages";
        $ch = curl_init();
        $headers   = [
            "Authorization: Bearer " . $this->ust,
            "Content-Type: application/json"
        ];

        $files = $this->upload_files();

        $payload = [
            [
                "sku" => "doofinder/doofinder-magento2",
                #"action" => ["technical" => "submit"],
                "action" => ["technical" => "draft"],
                "type" => "extension",
                "platform" => "M2",
                "version_compatibility" => [
                    [
                        "edition" => "CE",
                        "versions" => ["2.3", "2.4"]
                    ],
                    [
                        "edition" => "EE",
                        "versions" => ["2.3", "2.4"]
                    ]
                ],
                "name" => "Doofinder Site Search",
                "release_notes" => $this->release_notes,
                "version" => $this->version,
                "artifact" => [
                    "file_upload_id" =>  $files[0]
                ],
                "documentation_artifacts" => [
                    "user" => [
                        "file_upload_id" => $files[1]
                    ]
                ]
            ]
        ];


        echo "Create Release\n";
        echo "Release data:\n";
        var_dump($payload);
        echo "\n";

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_URL            => self::API_PATH . $url,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($payload)
            ]
        );

        $result   = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        var_dump($result);
        if (!empty($result)) {
            $result = reset($result);
        }

        if (property_exists($result, 'code')) {
            file_put_contents("release_result.txt", $result->code);
            if ($result->code === 200) {
                file_put_contents("release_result.txt", $result->code);
                echo "-------------------------------\n";
                echo " Release finished successfully. \n";
                echo " - Submission id: {$result->submission_id}\n";
                echo "-------------------------------\n";
            } elseif (property_exists($result, 'message')) {
                echo "---------------------------------------------\n";
                echo " An error ocurred while creating the release \n";
                echo " - Error Code: {$result->code}\n";
                echo " - Error Message: {$result->message}\n";
                echo "---------------------------------------------\n";
            }
        }
    }
}

function init($argv)
{
    $version = $argv[1];
    $changelog = $argv[2];
    $user = $argv[3];
    $secret = $argv[4];

    $client = new MagentoReleaseClient($version, $changelog, $user, $secret);
    $client->create_release();
}

init($argv);
