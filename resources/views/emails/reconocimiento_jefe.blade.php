<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Emailing Reconocimientos</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body bgcolor="#f9f8f8">
<div style="max-width: 600px; margin: 0 auto;">
  <table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" bgcolor="#ffffff">
    <tbody>
      <tr>
        <td style="padding-left:25px; padding-right:25px;" align="center">
          <img src="http://35.175.20.30/storage/empresas/{{$empresa->logo}}"  style="max-width: 100%;" alt=""/>
          <hr style="border-color:#ffffff;">
        </td>
      </tr>
      <tr>
        <td align="left" style="padding-left:25px; padding-right:25px;">
          <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 1.5; color: #1F1F1F; margin: 0px; font-size: 18px; padding-bottom: 10px; padding-top: 15px;">
            <strong>{{$params['name_reconocido']}}</strong> fue reconocido por <strong>{{$params['name_voto']}}</strong> por el motivo <strong>{{$params['opciones']}}</strong>
          </p>
          <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 1.5; color: #02733e; margin: 0px; font-size: 18px; padding-bottom: 10px; padding-top: 15px;">
            <strong>Justificación:</strong> {{$params['observaciones']}}
          </p>
          <hr/>
        </td>
      </tr>
      <tr>
        <td style="padding:25px;" align="center">
          <strong>
            <a href="http://35.175.20.30/login" style="color: #ffffff; text-decoration: none; margin: 0px; text-align: center; vertical-align: baseline; border: 4px solid #03894a; padding: 6px 25px; font-size: 15px; font-family: Arial, sans-serif; line-height: 21px; background-color: #03894a; border-radius: 10px; display: inline-block;">
              Ingresar
            </a>
          </strong>
        </td>
      </tr>
      <tr>
        <td height="20" align="center" bgcolor="#02733e">
          <p style="font-family: Arial, sans-serif; font-weight: normal; line-height: 19px; color: #ffffff; margin: 0px; font-size: 16px; padding: 5px;">
            {{$empresa->razon_social}}! - Plataforma de Reconocimientos
          </p>
        </td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
