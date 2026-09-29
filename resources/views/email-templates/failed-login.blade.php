<?php
use App\Models\GeneralSetting;
$generalSetting             = GeneralSetting::find('1');
?>
<!doctype html>
<html lang="en">
  <head>
    <title><?=$generalSetting->site_name?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  </head>
  <body style="padding: 0; margin: 0; box-sizing: border-box;">
    <section style="padding: 80px 0; height: 80vh; margin: 0 15px;">
        <div style="max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 15px; padding: 20px 15px; box-shadow: 0 0 30px -5px #ccc;">
          <div style="text-align: center;">
              <img src="https://keylines.net.in/dev/stumento_html/assets/images/logo.png" alt="" style=" width: 100%; max-width: 250px;">
          </div>
          <div>
            <h3 style="text-align: center; font-size: 25px; color: #5c5b5b; font-family: sans-serif;">Sorry, your signin attempt was failed</h3>
            <table style="width: 100%;  border-spacing: 2px;">
              <tbody>
                <tr>
                    <th style="background: #ccc; color: #000; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Email</th>
                    <td style="padding: 10px; background: #ccc; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;"><?=$email?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div style="border-top: 2px solid #ccc; margin-top: 50px; text-align: center; font-family: sans-serif;">
          <div style="text-align: center; margin: 15px 0 10px;"><?=$generalSetting->site_name?></div>
          <div style="text-align: center; margin: 15px 0 10px;">Phone: <?=$generalSetting->site_phone?></div>
          <div style="text-align: center; margin: 15px 0 10px;">Email: <?=$generalSetting->site_mail?></div>
        </div>
      </div>
    </section>
  </body>
</html>