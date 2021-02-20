<html>
<head>
<meta charset="utf-8"/>
<script type="text/javascript">
window.onload=function(){
    btn.onclick = e => {
        document.getElementById('text').select();
        if(document.execCommand('copy')) {
            window.open("http://argus-ktp.pr.rt.ru:8080/argus/", '_self');
        }
    }
}
</script>
</head>
<body>
<?php 
if(isset($_GET['argus'])) {
    $argus = $_GET['argus'];
    echo '<input id="text" type="text" value="' . $argus . '">';
    echo '<button id="btn">Copy</button>';
}
?>
</body>
</html>
