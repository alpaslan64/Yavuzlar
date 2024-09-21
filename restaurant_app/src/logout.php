<?php
session_start();
session_destroy();
?>
<script type="text/javascript">
    alert("Oturum Sonlandı!!!");
    window.location.href = 'index.php';
</script>
