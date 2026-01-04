<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>Contact Us</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Tahoma", Arial, sans-serif;
      background-color: #f4f6fb;
      direction: rtl;
    }

    table {
      border-collapse: collapse;
    }

    .container {
      width: 100%;
      max-width: 600px;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .header {
      
            background:linear-gradient(135deg, #f97316, #ea580c);
      text-align: center;
      padding: 24px;
      font-size: 22px;
      font-weight: bold;
      color: #ffffff;
    }

    .content {
      padding: 30px;
      color: #333333;
      font-size: 15px;
      line-height: 1.8;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
        padding: 14px 28px;
        background: linear-gradient(90deg, #6f00ff, #00d4ff);
      color: #fff !important;
      text-decoration: none;
      border-radius: 50px;
      font-size: 16px;
      font-weight: bold;
      transition: opacity 0.3s ease;
    }

    .btn:hover {
      opacity: 0.85;
    }

    .footer {
      text-align: center;
      padding: 18px;
      background-color: #f1f3f9;
      color: #777;
      font-size: 12px;
      border-top: 1px solid #e2e5ec;
    }

    @media only screen and (max-width: 620px) {
      .container {
        width: 95% !important;
      }

      .content {
        padding: 20px !important;
      }

      .btn {
        display: block !important;
        width: 100% !important;
        text-align: center !important;
      }
    }
  </style>
</head>
<body>

  <table align="center" width="100%" cellpadding="0" cellspacing="0" style="padding: 20px 0;">
    <tr>
      <td align="center">
        <table class="container" cellpadding="0" cellspacing="0">
          
          <!-- Header -->
          <tr>
            <td class="header">
              Welcome {{ $contact->name }} 👋
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="content">
              <p>Thank you for contacting us at <strong>Pizza & Gyro Party</strong> 🌟</p>
              <p>Your message has been received and we will get back to you soon from a team of experts.  
              We are always here for you to help achieve your goals with professionalism.</p>
              <p>We hope you have a great experience with us!</p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td class="footer">
              &copy; {{ date('Y') }} <strong>Pizza & Gyro Party</strong> — All rights reserved.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
