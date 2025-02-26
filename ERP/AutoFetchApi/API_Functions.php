<?php

class API_Functions
{
    /**
     * @param $data
     * @param string $format
     * @return mixed
     * Return HTTP Response
     */
    public function HttpResponse($data, $format = 'json')
    {
        if ($format == 'json') {
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit();
        }

        if($format == "plain") {
            echo $data;
            exit();
        }

        return $data;
    }

    /**
     * Check whether the given application exixts or not
     * @param $conn
     * @return mixed
     */
    public function checkForApplicationIDExists($conn) {
        try {
            $application_id = $_GET['_id'];

            if(empty($application_id))
                throw new Exception();

            $sql = "SELECT COUNT(*) cnt FROM 0_debtor_trans_details WHERE application_id = '$application_id' and quantity <> 0";

            $get = $conn->query($sql);

            if(!$get)
                throw new Exception();

            $result = $get->fetch_assoc();

            $response = "NOT_EXISTS";

            if($result['cnt'] > 0)
                $response = "EXISTS";

            return $this->HttpResponse($response,"plain");
        } catch (Exception $e) {
            return $this->HttpResponse(['status' => 'FAIL', 'msg' => 'SOMETHING_WENT_WRONG']);
        }
    }
}