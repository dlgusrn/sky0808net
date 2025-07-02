<?
    if(!isset($_SESSION['name'])){
        echo "<script>location.href='/live/'</script>";
    }