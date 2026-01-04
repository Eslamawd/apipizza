<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>Email confirmation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin:0; 
      padding:0; 
      font-family:Arial, sans-serif; 
      background-color:#0d0b14; 
      direction:rtl;
    }
    table {
      border-collapse:collapse;
    }
    .container {
      width:100%; 
      max-width:600px;
    }
    .header {
      background: linear-gradient(135deg, #f97316, #ea580c);
    }
    .btn {
      display:inline-block; 
      padding:14px 28px; 
      
      background: linear-gradient(135deg, #f97316, #ea580c);
      color:#fff !important; 
      text-decoration:none; 
      border-radius:50px; 
      font-size:16px; 
      font-weight:bold;
      box-shadow:0 4px 12px rgba(109,40,217,0.3);
      transition: opacity 0.3s ease;
    }
    .btn:hover {
      opacity:0.9;
    }
    @media only screen and (max-width:620px) {
      .container {
        width:100% !important; 
      }
      .btn {
        display:block !important;
        width:100% !important;
        text-align:center !important;
      }
      .padding-mobile {
        padding:20px !important;
      }
    }
  </style>
</head>
<body>

  <table align="center" width="100%" cellpadding="0" cellspacing="0" bgcolor="#0d0b14" style="padding:20px 0;">
    <tr>
      <td align="center">
        <table class="container" width="600" cellpadding="0" cellspacing="0" bgcolor="#161320" style="border-radius:16px; overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td class="header" align="center" style="padding:22px; font-size:22px; font-weight:bold; color:#ffffff; border-radius:16px 16px 0 0;">
              Welcome {{ $user->name }} 👋
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="padding-mobile" style="padding:30px; color:#e6e0f8; line-height:1.8; font-size:15px;">
              <p style="margin:0 0 15px 0;">Thank you for joining us<strong>Pizza & Gyro Party</strong>.</p>
              <p style="margin:0 0 25px 0;">Please click the button below to confirm your email and activate your account:</p>

              <div style="text-align:center; margin:30px 0;">
                <a href="{{ $url }}" class="btn">✅ Email confirmation</a>
              </div>

              <p style="font-size:14px; color:#a59dc4; margin-top:25px;">
                If you did not create an account, you can ignore this message.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td bgcolor="#0d0b14" align="center" style="padding:20px; color:#8b7cae; font-size:12px; border-top:1px solid #2e2840;">
              &copy; {{ date('Y') }} <strong>Pizza & Gyro Party</strong>. All rights reserved.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
