<?php

// http://localhost/erp/customer_feedback.php?cntr=10&dep=3

use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$path_to_root = "./ERP";

require $path_to_root . '/laravel/bootstrap/autoload.php';

function getDBConnection()
{
    include $GLOBALS['path_to_root'] . '/config_db.php';

    [
        "host" => $host,
        "dbname" => $db_name,
        "dbuser" => $username,
        "dbpassword" => $password
    ] = $db_connections[0];

    $con = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);

    return $con;
}

try {
    $qArr = array(
        "q1" => "How was your experience in Alyalyis?",
        "q2" => "How did you hear about us?",
        "q3" => "What would you say about our service team members and management?",
        "q4" => "Would you recommed Alyalayis to a friends?",
        "q5" => "Any comments or suggestions."
    );

    $con = getDBConnection();


    if (!empty($_GET['cntr'])) $_POST['cntr'] = $_GET['cntr'];
    if (!empty($_GET['dep'])) $_POST['dep'] = $_GET['dep'];
    $deptName = '';

    $query = "SELECT id, name FROM `0_dimensions` WHERE id = :id ";
    $stmt = $con->prepare($query);
    $stmt->execute(['id' => $_POST['dep']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $deptName = $row['name'];

    function sendEmail($to)
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();                                              //Send using SMTP
        $mail->Host       = 'smtp.office365.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                     //Enable SMTP authentication
        $mail->Username   = 'y.feedback@aygtc.ae';                    //SMTP username
        $mail->Password   = 'Y.Fee_@yGtc22';                          //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           //Enable implicit TLS encryption
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('y.feedback@aygtc.ae', 'Al Yalayis Govt. Transaction Center');
        // $mail->addAddress('joe@example.net', 'Joe User');         //Add a recipient
        $mail->addAddress($to);                                      //Name is optional

        //Content
        $mail->isHTML(true);                                         //Set email format to HTML
        $mail->Subject = 'Feedback';
        $mail->Body    = ("Dear Customer, </br>"
            . "<p> Thanks for the feedback on your experience with our customer support team. We sincerely appreciate your insight because it helps us build a better customer experience.</p>"
            . "<p> Feedback like this helps us constantly improve our customer experiences by knowing what we are doing right and what we can work on. So, I appreciate you taking the time to send us this helpful response.</p>"
            . "<p> Don't hesitate to reach out if you have any more questions, comments, or concerns info@aygtc.ae</p>"
            . "Regard, <br>"
            . "Alyalayis Govt. Transaction Centre. <br>");

        $mail->send();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $con = getDBConnection();

        $ans1 = htmlspecialchars(strip_tags($_POST['howExp']));
        $ans2 = htmlspecialchars(strip_tags($_POST['ddlHowHear']));
        $ans2_1 = htmlspecialchars(strip_tags($_POST['txtHowHear']));

        if ($ans2 == 'other' && $ans2_1 != '') {
            $ans2 = $ans2_1;
        }

        $ans3 = htmlspecialchars(strip_tags($_POST['service']));
        $ans4 = htmlspecialchars(strip_tags($_POST['recommend']));
        $ans5 = htmlspecialchars(strip_tags($_POST['comment']));

        $data = [];
        foreach (range(1, 5) as $i) {
            $data[] = [
                "q" => $qArr["q{$i}"],
                "a" => ${"ans$i"}
            ];
        }
        $data = json_encode($data);

        $query = "INSERT INTO 0_customer_feedback ( counter_no, department_id, feed_back ) VALUES (:cntr, :dep, :fdbk)";
        $stmt = $con->prepare($query);
        $result = $stmt->execute(['cntr' => $_POST['cntr'], 'dep' => $_POST['dep'], 'fdbk' => $data]);
        $result ? sendEmail($_POST['email']) : ($error = true);
    }
} catch (Exception $e) {
    $error = true;
    echo "Error: {$e->getMessage()}";
}
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alyalayis Govt. Transaction Center</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.css">

    <style>
        input[type=text],
        select {
            width: 100%;
            padding: 10px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type=submit] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type=submit]:hover {
            background-color: #45a049;
        }

        div {
            background-color: #f2f2f2;
            padding: 5px;
            margin: auto;
        }

        .container {
            width: 100%;
            align-content: center;
            border: 0px solid green;
            width: 100%;
        }
    </style>

    <style>
        /* for survey */
        /* Styling the Body element i.e. Color,
        Font, Alignment */
        body {
            background-color: #05c46b;
            font-family: Verdana;
            text-align: center;
        }

        /* Styling the Form (Color, Padding, Shadow) */
        form {
            background-color: #fff;
            max-width: 500px;
            margin: 5px auto;
            padding: 30px 20px;
            box-shadow: 2px 5px 10px rgba(0, 0, 0, 0.5);
        }

        /* Styling form-control Class */
        .form-control {
            text-align: left;
            margin-bottom: 20px;
        }

        /* Styling form-control Label */
        .form-control label {
            display: block;
            margin-bottom: 10px;
        }

        /* Styling form-control input,
        select, textarea */
        .form-control input,
        .form-control select,
        .form-control textarea {
            border: 1px solid #777;
            border-radius: 2px;
            font-family: inherit;
            padding: 10px;
            display: block;
            width: 95%;
        }

        /* Styling form-control Radio
        button and Checkbox */
        .form-control input[type="radio"],
        .form-control input[type="checkbox"] {
            display: inline-block;
            width: auto;
        }

        /* Styling Button */
        button {
            background-color: #05c46b;
            border: 1px solid #777;
            border-radius: 2px;
            font-family: inherit;
            font-size: 21px;
            display: block;
            width: 100%;
            margin-top: 30px;
            margin-bottom: 5px;
        }
    </style>

    <style>
        * {
            box-sizing: border-box;
        }

        .column {
            float: left;
            /* width: 33.33%; */
            width: 9%;
            padding: 5px;
        }

        /* Clearfix (clear floats) */
        .row::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Responsive layout - makes the three columns stack on top of each other instead of next to each other */
        @media screen and (max-width: 500px) {
            .column {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>

</head>

<body>
    <div style="background-color: white;">
        <picture>
            <img src="<?= $path_to_root ?>/../assets/images/ybc_logo.png" alt="Alyalayis Govt. Transaction Center" style="height: 3.8rem;">
            Alyalayis Govt. Transaction Center
        </picture>
    </div>

    <!-- <div class="container mt-3">
        <h5>Big News!</h5>
        Give <a href="serveyShrt1.php">30-seconds</a> Survey and win <b style="color: #4CAF50;">Free Coffee!</b>
        <br>
        Give <a href="#">01-minute</a> Survey and win <b style="color: #4CAF50;">10 Dirham!</b>
    </div> -->

    <div class="container mt-3">

        <div>
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') :
                if (!empty($error)) {
                    echo "<div class='alert alert-danger'>Something went wrong, Please try again.</div>";
                } else {
                    echo "<div class='alert alert-success'>Thank you for your valuable Feedback.</div>";
                } else : ?>
                <form id="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="cntr" value="<?= $_POST['cntr'] ?>">
                    <input type="hidden" name="dep" value="<?= $_POST['dep'] ?>">

                    <div class="form-control">
                        <label> Counter No: <?= $_POST['cntr'] ?> </label>
                        <label> Department: <?= $deptName ?> </label>
                    </div>

                    <div class="form-control">
                        <label for="email"> Email </label>
                        <input type="email" placeholder="Enter your email" id="email" name="email" required>
                    </div>

                    <div class="form-control">
                        <label for="howExp" id="lblHowExp"> <?= $qArr['q1'] ?> </label>
                        <select name="howExp" id="howExp" required>
                            <option value="">--Select--</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Notgood">Not good</option>
                            <option value="Verybad">Very bad</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="ddlHowHear" id="lblHowHear"> <?= $qArr['q2'] ?> </label>
                        <select name="ddlHowHear" id="ddlHowHear" required onchange="funHowHear(this.value)">
                            <option value="">--Select--</option>
                            <option value="Social media (Twitter, Facebook, etc.)">Social media (Twitter, Facebook, etc.)</option>
                            <option value="Heard on radio">Heard on radio</option>
                            <option value="Recommendation from a friend">Recommendation from a friend</option>
                            <option value="Heard on TV">Heard on TV</option>
                            <option value="From article">From article</option>
                            <option value="Google Ad">Google Ad</option>
                            <option value="Internet">Internet</option>
                            <option value="Sales Person">Sales Person</option>
                            <option value="Seen Alyalayis Center">Seen Alyalayis Center</option>
                            <option value="other">Other <small>(Please specify)<small></option>
                            <textarea style="display: none;" name="txtHowHear" id="txtHowHear" placeholder="Please enter other source here."></textarea>
                        </select>
                    </div>

                    <div class="form-control">
                        <label id="lblService"> <?= $qArr['q3'] ?> </label>
                        <label for="service-1">
                            <input type="radio" id="service-1" name="service" required value="Very good"> Very good</input></label>
                        <label for="service-2">
                            <input type="radio" id="service-2" name="service" value="Good somehow"> Good somehow</input></label>
                        <label for="service-3">
                            <input type="radio" id="service-3" name="service" value="Non professional"> Non professional</input></label>
                    </div>

                    <div class="form-control">
                        <label id="lblFriends"> <?= $qArr['q4'] ?> </label>
                        <label for="friends-1">
                            <input type="radio" id="friends-1" name="recommend" value="Yes" required> Yes</input></label>
                        <label for="friends-2">
                            <input type="radio" id="friends-2" name="recommend" value="No"> No</input></label>
                        <label for="friends-3">
                            <input type="radio" id="friends-3" name="recommend" value="Maybe"> Maybe</input></label>
                    </div>

                    <div class="form-control">
                        <label for="comment" id="lblComment"> <?= $qArr['q5'] ?> <small>Minimum 100 character.<small></label>
                        <textarea name="comment" id="comment" placeholder="Enter your comment here" minlength="5" required></textarea>
                    </div>

                    <button type="submit" value="submit">Submit</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- <br> -->
        <!-- <h5 style="text-align: center;">Departments in Alyalayis</h5> -->
        <div class="row" style="background-color: white;">
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/tasheel_logo.png" alt="TAS-HEEL" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/amer_logo.jpg" alt="AMER" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/ybc_logo.png" alt="TYPING CENTER" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/rta_logo.png" alt="RTA" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/dha_logo.png" alt="DHA" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/dubai_court_logo.jpg" alt="DUBAI COURT" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/ded_logo.png" alt="DED" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/al_adheed.png" alt="AL ADHEED" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/ejari.png" alt="EJARI" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/tawjeeh_logo.jpg" alt="TAWJEEH" style="width:100%">
            </div>
            <div class="column">
                <img src="<?= $path_to_root ?>/../assets/images/tadbeer_logo.png" alt="TADBEER" style="width:100%">
            </div>
        </div>

    </div>

    <script src="<?= $path_to_root ?>/../assets/plugins/general/jquery/dist/jquery-3.5.1.min.js"></script>
    <script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {
            $('#form').parsley().on('field:validated', function() {
                var ok = $('.parsley-error').length === 0;
                $('.bs-callout-info').toggleClass('hidden', !ok);
                $('.bs-callout-warning').toggleClass('hidden', ok);
            })
        });

        function funHowHear(value) {
            if (value == 'other') {
                document.getElementById("txtHowHear").style.display = 'inline';
            } else {
                document.getElementById("txtHowHear").style.display = 'none';
            }
        }
    </script>

    <!-- <script type="text/javascript">
        // function validate() {
        //     if (document.getElementById("howExp").value == 'select')
        //     {
        //         //  alert(document.getElementById("howExp").value);
        //         document.getElementById("txtHowHear").required = true;
        //         return;
        //     }
        //      else if (document.getElementById("howExp").value == 'select')
        //      {
        //         // alert('Please select Login Option...!');
        //         document.getElementById("howExp").required = true;
        //         return;
        //     }

        //     if ($rdb == "user" && (document.getElementById("name").value == "" || document.getElementById("pass").value == "")) {
        //         alert("Please enter User Name or Passwrd...!");
        //         return;
        //     } else if ($rdb == "mob" && document.getElementById("mobNo").value == "") {
        //         alert("Please enter Mobile Number...!");
        //         return;
        //     } else if ($rdb == "cust" && document.getElementById("custID").value == "") {
        //         alert("Please enter Customer ID...!");
        //         return;
        //     }
        // }
    </script> -->

</body>

</html>