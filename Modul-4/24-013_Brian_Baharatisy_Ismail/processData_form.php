<!doctype html>
<html>
<head>
    <title>Register</title>
    <style>
        .field { margin-bottom: 10px; }
        label { display: inline-block; width: 120px; }
    </style>
</head>
<body>
    <h2>Register</h2>
    <fieldset>
        <legend>Person Detail</legend>

<?php
$errors = array();
if (isset($_POST['submit']))
{
    require 'validate.inc';
    validateName($errors, $_POST, 'surname');
    validateEmail($errors, $_POST, 'email'); // Validasi tambahan untuk email
    
    if ($errors)
    {
        echo '<h1>Invalid, correct the following errors:</h1>';
        foreach ($errors as $field => $error)
            echo "$field: $error<br/>";
        
        include 'form.inc';
    }
    else
    {
        echo 'Form submitted successfully with no errors';
    }
}
else
{
    include 'form.inc';
}
?>

    </fieldset>

</body>
</html>