<?php
session_start();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="chatbot_container">
    <h1>A simple chatbot</h1>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=chatbot", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        echo "Connection failed: " . $e->getMessage();
    }

    $ignore_list = array("is", "of", "were", "in", "the", "are", "there");
    $interogative_pronoun = array("what", "what's", "which", "who", "whose", "whom", "when", "why",
        "where", "how", "howmany", "howmuch");
    if(isset($_POST['user_message']) && !empty($_POST['user_message'])) {
        $keywords = array();
        $temp = preg_split("/\s+/", $_POST['user_message']);

        if ((stripos($_POST['user_message'], 'hi') !== false && strlen($_POST['user_message']) < 5)) {
            echo "Hi! I am chatbot. Ask your question.";
        } elseif (substr(rtrim($_POST['user_message']), -1) == '.') {
            $temp = trim($_POST['user_message']);
            $temp_s_keywords = $_SESSION["s_keywords"];
            $sql = "INSERT INTO db(Keywords, Value) VALUES('$temp_s_keywords', '$temp');";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        } else {
            if (in_array($temp[0] . $temp[1], $interogative_pronoun, true)) {
                array_push($keywords, $temp[0] . $temp[1]);
            } elseif (in_array($temp[0], $interogative_pronoun, true)) {
                array_push($keywords, $temp[0]);
            } else {
                array_push($keywords, $temp[0]);
                array_push($keywords, $temp[1]);
            }
            if (end($temp) == "") {
                array_pop($temp);
            }

            for ($i = 2; $i < count($temp); $i++) {
                if (in_array($temp[$i], $ignore_list, true)) {
                } else {
                    array_push($keywords, $temp[$i]);
                }
            }
            if (substr(end($keywords), -1) == "?") {
                $temp_txt = substr(end($keywords), 0, -1);
                array_pop($keywords);
                array_push($keywords, $temp_txt);
                //array_push($keywords, "?");
                $sql = "SELECT Value FROM db WHERE ";
                $temp_count = count($keywords) - 1;
                for ($i = 0; $i < count($keywords) - 1; $i++) {
                    $sql = $sql . "Keywords LIKE '%$keywords[$i]%' AND ";
                }
                $sql = $sql . "Keywords LIKE '%$keywords[$temp_count]%';";
                //$sql = "INSERT INTO db(Keywords) Value('$keywords');";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchColumn();
                $_SESSION["s_keywords"] = implode($keywords, " ");
                if ($result == "") {
                    echo "I have no answer for this question. Please Help Me!";
                } else {
                    echo $result;
                }
            }
        }
    }
    ?>

    <form action="" method="POST">
        <input id="user_message" name="user_message" type="text">
        <input type="submit" value="Send">
    </form>
</div>
</body>
</html>