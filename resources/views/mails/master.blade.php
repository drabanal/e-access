<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>E-Access</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; font-family: 'Open Sans', sans-serif">
<table border="0" cellpadding="50" cellspacing="0" width="100%" bgcolor="#f0f0f0">
    <tr>
        <td align="center">
            <table width="620" border="0" cellpadding="0" cellspacing="0" style="-webkit-box-shadow: 0px 5px 25px 5px rgba(0,0,0,0.25);-moz-box-shadow:  0px 5px 25px 5px rgba(0,0,0,0.25);box-shadow:  0px 5px 25px 5px rgba(0,0,0,0.25);">
                <tr>
                    <td>
                        <table width="100%" border="0" cellpadding="20" cellspacing="0" bgcolor="#ffffff">
                            <tr>
                                <td align="center" style="font: 13px 'Helvetica Neue', Helvetica, sans-serif; line-height: 22px;">
                                    <a href="http://www.ingenuiti.com">
                                        <img src="https://buko.ingenuiti.com/assets/images/ingenuiti_logo.png" width="200px">
                                    </a><br />  
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width="100%" border="0" cellpadding="2" cellspacing="0" bgcolor="#00A2E3" style="border-radius: 4px 4px 0 0;">
                            <tr><td></td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width="100%" border="0" cellpadding="20" cellspacing="0" bgcolor="#ffffff">
                            <tr>
                                <td align="left" style="font-size: 13px; line-height: 22px; padding-bottom: 0px; padding-left: 20px;">
                                    @yield('content')
                                </td>
                            </tr>
                            <tr>
                                <td class="thank-you-row" style="font-size: 13px; padding-top: 0px; padding-bottom: 0px;">
                                    <p>Thank you.</p>
                                    <p style="color: #909090; margin: 1em 0;"><em>&ndash; the ingenuiti team</em></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table width="620" border="0" cellpadding="0" cellspacing="0" >
                <tr>
                    <td>
                        <table width="100%" border="0" cellpadding="20" cellspacing="0">
                            <tr>
                                <td></td>
                                <td align="right" style="font-size: 13px; color: #666666;">
                                    {{ \Carbon\Carbon::now()->format('F j, Y') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>                                                            
            </table>
        </td>
    </tr>
</table>
</body>
</html>