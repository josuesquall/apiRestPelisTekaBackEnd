<?php
$idc=0;
if (isset($_REQUEST['m'])){
    $idc=$_REQUEST['m'];
}
//inicio para escribir codigo html
print('<div class="container" id="container" onmouseover="closeNav();"><div class="PostContent">');
if(isset($idc) and $idc>0){
    ?>
    <script type="text/javascript">
    var __SESSION=<?= json_encode($_SESSION["login"])?>;
    core.callApiRest({requestMethod:'POST',module:'<?php print $_SESSION['modulos'][$idc]['path']; ?>',method:'showView',view:'inicio',objetoDom:'.PostContent'});
    </script>
    <?php
} else {
    ?>
    <script type="text/javascript">
    var __SESSION=<?= json_encode($_SESSION["login"])?>;
    core.callApiRest({requestMethod:'POST',module:'<?php print $_SESSION['login']['run']; ?>',method:'showView',view:'inicio',objetoDom:'.PostContent'});
    </script>
    <?php
}
print('</div><div class="cleared"></div>');
print('</div>');
?>