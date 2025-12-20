<?php
session_start();
session_unset();
session_destroy();
?>

<script>
  alert("✅ Successfully logged out!");
  // redirect to login page
  window.location.href = "../index.php"; // change path if needed
</script>
