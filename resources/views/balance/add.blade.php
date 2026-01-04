<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إشعار من QREGY</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Arial", sans-serif;
      background-color: #f4f6fb;
      color: #333;
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
      background: linear-gradient(90deg, #007bff, #1abc9c);
      color: #fff;
      text-align: center;
      padding: 22px;
      font-size: 22px;
      font-weight: bold;
    }

    .content {
      padding: 30px;
      font-size: 15px;
      line-height: 1.8;
      color: #444;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
      padding: 14px 28px;
      background: linear-gradient(90deg, #1abc9c, #00bcd4);
      color: #fff !important;
      text-decoration: none;
      border-radius: 50px;
      font-size: 16px;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: linear-gradient(90deg, #00bcd4, #1abc9c);
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
        display: block;
        width: 100%;
        text-align: center;
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
              مرحباً 👋
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="content">
              <p>شكراً لتواصلك معنا في <strong>QREGY</strong>.</p>
              <p>
                تمت إضافة الرصيد 
                <strong style="color:#1abc9c;">{{ $balance }}</strong> 
                إلى محفظتك بنجاح 💰.<br>
                يُرجى التحقق من محفظتك، وشكراً لك!<br>
                نحن دائماً بجانبك خطوة بخطوة لتحقيق النجاح 🌟
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td class="footer">
              &copy; {{ date('Y') }} <strong>QREGY</strong> — جميع الحقوق محفوظة.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
