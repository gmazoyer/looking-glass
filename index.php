<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="keywords" content="Looking Glass, LG, BGP, prefix-list, AS-path, ASN, traceroute, ping, IPv4, IPv6, Cisco, Juniper, Internet" />
  <meta name="description" content="<?php echo $config['frontpage']['title']; ?>" />
  <title><?php echo $config['frontpage']['title']; ?></title>
  <link href="bootstrap-3.1.1/css/bootstrap.min.css" rel="stylesheet" />
  <link href="bootstrap-3.1.1/css/bootstrap-theme.min.css" rel="stylesheet" />
  <link href="<?php echo $config['frontpage']['css']; ?>" rel="stylesheet" />
</head>

<body>
  <div class="header_bar">
    <h1><?php echo $config['frontpage']['title']; ?></h1><br />
    <?php
    if (isset($config['frontpage']['image'])) {
      echo '<img src="'.$config['frontpage']['image'].'" alt="logo" />';
    }
    ?>
  </div>

  <div class="content" id="command_options">
    <form role="form" action="execute.php" method="post">
      <div class="form-group">
        <label for="routers">Router to use</label>
        <select size="5" class="form-control" name="routers">
        <?php
          $first = true;
          foreach (array_keys($config['routers']) as $router) {
            if ($first) {
              $first = false;
              echo '<option value="'.$router.'" selected="selected">'.
                $config['routers'][$router]['desc'].'</option>';
            } else {
              echo '<option value="'.$router.'">'.
                $config['routers'][$router]['desc'].'</option>';
            }
          }
        ?>
        </select>
      </div>

      <div class="form-group">
        <label for="query">Command to issue</label>
        <select size="5" class="form-control" name="query" id="query">
          <option value="bgp" selected="selected">show route IP_ADDRESS</option>
          <option value="as-path-regex">show route as-path-regex AS_PATH_REGEX</option>
          <option value="as">show route AS</option>
          <option value="ping">ping IP_ADDRESS</option>
          <option value="traceroute">traceroute IP_ADDRESS</option>
        </select>
      </div>

      <div class="form-group">
        <label for="parameters">Parameters</label>
        <input class="form-control" name="parameters" id="input-params" />
      </div>

      <div class="confirm btn-group btn-group-justified">
        <div class="btn-group">
          <button class="btn btn-primary" id="send" type="submit">Enter</button>
        </div>
        <div class="btn-group">
          <button class="btn btn-danger" id="clear" type="reset">Reset</button>
        </div>
      </div>
    </form>
  </div>

  <div class="loading">
    <div class="progress progress-striped active">
      <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
      </div>
    </div>
  </div>

  <div class="result">
    <pre class="pre-scrollable" id="output"></pre>
    <div class="reset">
      <button class="btn btn-danger btn-block" id="backhome">Reset</button>
    </div>
  </div>

  <div class="footer_bar">
    <p class="text-center">
      <?php
          if (isset($config['frontpage']['disclaimer']) &&
            !empty($config['frontpage']['disclaimer'])) {
        echo 'Your IP address: '.$_SERVER['REMOTE_ADDR'].'<br />';
        echo $config['frontpage']['disclaimer'];
        echo '<br /><br />';
      }

      if (isset($config['contact']) && !empty($config['contact'])) {
        echo 'Contact:&nbsp;';
        echo '<a href="mail:'.$config['contact']['mail'].'">'.$config['contact']['name'].'</a>';
      }
      ?>
    </p>
  </div>

  <!-- jquery / bootstrap / custom functions -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="bootstrap-3.1.1/js/bootstrap.min.js"></script>
  <script src="includes/utils.js"></script>
  </script>
</body>
</html>
