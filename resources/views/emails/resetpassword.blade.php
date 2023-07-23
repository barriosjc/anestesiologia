<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Emailing Reset Password</title>
<style>
  @media only screen and (max-width: 600px) {
    .container {
      width: 100% !important;
    }
  }
</style>
</head>

<body bgcolor="#f9f8f8">
<table class="container" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
  <tbody>
    <tr>
      <td style="padding-left:25px; padding-right:25px;" align="center"><img src="images/logo-clap.png" width="171" height="84" alt=""/>
		<hr/ style="border-color:#ffffff;">
		</td>
    </tr>
	  
    <tr>
      <td align="left" style="padding-left:25px; padding-right:25px;">		  
        <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 19px; color: #1F1F1F; margin: 0px; font-size: 18px; padding-bottom: 10px; padding-top: 15px;"><strong>¡Bienvenido a Clap! {{$user->last_name}}</strong><strong></strong></p>
        <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 19px; color: #1F1F1F; margin: 0px; font-size: 18px; padding-bottom: 10px; padding-top: 15px;"><strong>Se le ha realizado un cambio de clave</strong><strong></strong></p>
        <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 19px; color: #02733e; margin: 0px; font-size: 18px; padding-bottom: 10px; padding-top: 15px;"><strong>Su nueva clave es: </strong>{{$clave}}</p>
        <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 19px; color: #02733e; margin: 0px; font-size: 18px; padding-bottom: 10px; padding-top: 15px;"><strong>Ingrese con esta nueva clave, luego el sistema le pedirá una nueva clave que pueda recordar </strong></p>
        <hr/>
      </td>
    </tr>
    <tr>
      <td style="padding:25px;" align="center"><strong><a href="%link%" style="color: #ffffff; text-decoration: none; margin: 0px; text-align: center; vertical-align: baseline; border: 4px solid #03894a; padding:  6px 25px; font-size: 15px; font-family: Arial, sans-serif; line-height: 21px; background-color: #03894a; border-radius: 10px;">Ingresar</a></strong></td>
    </tr>
    <tr>
      <td height="20" align="center" bgcolor="#02733e"><p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 19px; color: #ffffff; margin: 0px; font-size: 16px; padding: 5px;">Clap! - Plataforma de Reconocimientos</p></td>
    </tr>
  </tbody>
</table>
</body>
</html>
