<?php
/*
Template Name: Espacio usuario
*/

ini_set('error_reporting', E_ERROR  );
error_reporting(E_ERROR);
global $model;



// Si no conectado, no dejar pasar

$current_user = wp_get_current_user(); 
$current_user =object_to_array($current_user);
//	 print_r($current_user);
if(!$current_user ||!$current_user['data']['ID']){		
	header('location: /espacio-usuario-entrar');
}


$result = array();
$result['user_login'] = $current_user['data']['user_login'];
$email_user = $result['user_email'] = $current_user['data']['user_email'];
$result['admin'] = $current_user['data']['admin'];
$display_name = $result['display_name'] = $current_user['data']['display_name'];	 	 
$id_wordpress = $result['ID'] = $current_user['data']['ID'];	 	 
$result['id_wordpress'] = $current_user['data']['ID'];	

$es_autor = false;
$id_autor = $id_wordpress;
$result = mysql_query("select * from _autor where id_autor = '$id_autor'");	

if($result && mysql_num_rows($result)){
	$es_autor = true;
}


if(isset($_GET['descarga'])){
	//descargamos , pero antes comprobamos que la tenemos
	$id_articulo = $_GET['id_descarga'];

	if($id_articulo == 9){
		ini_set('memory_limit', '-1');	
		ini_set('max_execution_time', 1800); //30 mins 
		header("Content-Type: application/rar");
		$archivo = 'Radio_Bombs_Vol.2_Preview.zip';
		$folder = 'downloadable/';		   
		header('Content-Disposition: attachment; filename="'.$archivo.'"');
		readfile('/home/refracti/public_html/wp-content/downloadable/'.$archivo); 			 		
		die();
	} 

	$descarga = mysql_query("select * from _comprado c, _articulo a where c.id_wordpress= '$id_wordpress' and a.id_articulo=c.id_articulo and a.id_articulo = '$id_articulo'");	 	
	if(!$descarga || !mysql_num_rows($descarga)){	 		
		die("Hubo un fallo en la descarga, por favor, inténtalo de nuevo");	
	}
	$d = mysql_fetch_array($descarga);

	//php de descarrega
	//		        $archivo = 'Radio_Bombs_vol_1.rar';		        
	$archivo = $d['archivo'];
	$folder = 'downloadable/';

	//echo $folder.$archivo.'###'.$extension;
	ini_set('memory_limit', '-1');		
	ini_set('max_execution_time', 2800);	            
	set_time_limit(0);	
	/*

$file = @fopen($file_path,"rb");
while(!feof($file))
{
print(@fread($file, 1024*8));
ob_flush();
flush();
}

*/

	header('Content-Description: File Transfer');

	header("Content-Type: application/rar");
	header('Content-Disposition: attachment; filename="'.$archivo.'"');

	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	header('Accept-Ranges: bytes'); 
	$test = true;

	if($test){
		$fullname = '/home/refracti/public_html/wp-content/downloadable/'.$archivo;
		/*$fd = fopen($fullname, "rb");   	
while(!feof($fd)) {
$buffer = fread($fd, 1*(1024*1024));
echo $buffer;
ob_flush();
flush();    //These two flush commands seem to have helped with performance

}	 */  	



		$size = intval(sprintf("%u", filesize($fullname))); 
		$size = filesize($file);
		header("Content-Length: " . $size);
		$chunksize = 100 * (1024 * 1024); // how many bytes per chunk 
		if ($size > $chunksize) { 
			$handle = fopen($fullPath, 'rb'); 
			$buffer = ''; 
			while (!feof($handle)) { 
				$buffer = fread($handle, $chunksize); 
				echo $buffer; 
				ob_flush(); 
				flush(); 
			} 
			fclose($handle); 
			die();
		} else { 
			readfile($fullname); 
			die();
		}
		//	die();





	}else{
		/*			   ob_end_flush();
ob_clean();
flush();    */
		ob_start();          
		readfile('/home/refracti/public_html/wp-content/downloadable/'.$archivo); 			 				
		ob_end_flush();
		die();
		exit;
	}
}


if(isset($_POST['afiliado_info_pago']) && isset($_POST['afiliado_pago'])){
	$afiliado_info_pago = $_POST['afiliado_info_pago'];
	$afiliado_pago = $_POST['afiliado_pago'];		
	$result = mysql_query("update _afiliado set pago = '$afiliado_pago', info_pago='$afiliado_info_pago' where id_wordpress= '$id_wordpress'");	 	

}



if(isset($_GET['alta_afiliado'])){
	$result = mysql_query("select * from _afiliado where id_wordpress= '$id_wordpress'");
	$afiliado = false;
	if($result && !mysql_num_rows($result)){
		// lo damos de alta
		$correo = $email_user;
		$nombre = $display_name;
		$temp = explode("@", $correo);
		$nombre_aff = str_replace(' ','_',$nombre);
		$codigo = str_replace(' ','_',trim($nombre_aff).'_'.$temp[0].rand(0,999));
		$url = 'http://refractionproductions.com/?aff='.$codigo;
		$result = mysql_query("insert into _afiliado set correo = '$correo', nombre = '$nombre', contrasenya='', codigo = '$codigo', pago='', info_pago='', fecha = NOW(), id_wordpress='$id_wordpress'");						
		if(!$result) die ('hubo un problema con el alta, por favor inténtalo de nuevo');				
		$asunto = 'Bienvenido! Aqui están tus datos de tu cuenta de afiliados de Refraction Productions';
		$mensaje = 'Hola '.$nombre.',';
		$mensaje .= '<br><br>';
		$mensaje .= 'Gracias por incorporarte al programa de afiliados de refraction productions. A continuación tus datos';	
		$mensaje .= '<br><br>';
		$mensaje .= 'Tu código de afiliado es <strong style="font-weight:bold">'.$codigo.'</strong>.';	
		$mensaje .= '<br><br>';
		$mensaje .= 'El enlace al que tienes que enviar a los compradores es el siguiente:<br>';		
		$mensaje .= '<a href="'.$url.'">'.$url.'</a>';
		$mensaje .= '<br><br>';
		$mensaje .= 'Cuando se produzca una compra te avisaremos a este correo. Y siempre que quieras el informe de resultados puedes ir a http://refractionproductions.com/afiliados/ y consultarlo introduciendo tu correo. Ahí también podrás consultar las condiciones de pago de la cuenta';	
		$mensaje .= '<br><br>';
		$mensaje .= 'Y si tienes alguna duda, escríbenos a este correo en cualquier momento.';	
		$mensaje .= '<br><br>';
		$mensaje .= 'Gracias de nuevo por confiar en nuestro programa.';		
		$mensaje .= '<br><br>';
		$mensaje .= 'El equipo de Refraction Productions';				
		if(!send_mail($asunto,$mensaje,$correo)) {
			//				error('No se pudo enviar el correo.');
		}

	}
	// enviar correo


}

$articulos = $model->articulos();

$articulos = $model->articulos_activos();


$descargas = mysql_query("select * from _comprado c, _articulo a where c.id_wordpress= '$id_wordpress' and a.id_articulo=c.id_articulo ");

if($id_wordpress==1){
	$descargas = mysql_query("select * from  _articulo a  ");	
}

echo mysql_error();
if($descargas && mysql_num_rows($descargas)){
	$aux = array();
	while($d = mysql_fetch_array($descargas)){
		$aux[] = $d;
	}
	$descargas = $aux;
}else $descargas = false;


$descargas_ids = Array();
if($descargas){
	foreach($descargas as $d){
		$descargas_ids[] = $d['id_articulo'];
	}
}

//	print_r($descargas_ids);


unset($aux);
$pedidos = mysql_query("select *, pl.total as total_linea from _pedido p, _pedido_linea pl where p.id_wordpress= '$id_wordpress' and p.id_pedido = pl.id_pedido ");
echo mysql_error();
if($pedidos && mysql_num_rows($pedidos)){
	$aux2 = array();
	$last_id=-1;
	while($d = mysql_fetch_array($pedidos)){
		//	print_r($d);
		if($last_id!=$d['id_pedido']){ //nuevo pedido
			if(isset($aux)) $aux2[] = $aux; 
			$aux = array();
			$aux = $d; 
			$aux['lineas'] = array();
			$aux['lineas'][] = $d;				
		}else{
			$aux['lineas'][] = $d;				
		}
		$last_id = $d['id_pedido'];
	}
	$aux2[] = $aux; 		
	//print_r($aux2);
	$pedidos = array_reverse($aux2);
}else $pedidos = false;	


////////////// AFILIADO

$result = mysql_query("select * from _afiliado where id_wordpress= '$id_wordpress'");
$afiliado = false;
if($result && mysql_num_rows($result)>0){
	$afiliado = mysql_fetch_array($result);
	$nombre = $afiliado['nombre'];
	$correo = $afiliado['correo'];	
	$codigo = $afiliado['codigo'];	
	$id_afiliado = $afiliado['id_afiliado'];		

	$result = mysql_query("select * from _afiliado_lead where id_afiliado = '$id_afiliado'");

	if($result){
		$informe = '';
		$num_ventas = 0;
		$rows = '';
		$leads = 0;
		$total_ventas = '';
		$por_cobrar = '';		
		$redimidos = 0;
		$pendiente = '';
		$rows = false;
		if(mysql_num_rows($result)>0){
			while($r = mysql_fetch_array($result)){
				$leads++;
				if($r['pagado']){
					$num_ventas++;
					$is_redimido = 'no';
					$total_ventas += $r['total_pedido'];
					if($r['redimido']){
						$redimidos++;	
						$is_redimido = 'si';
					}else{
						$pendiente+=$r['comision'];
					}					
					$rows .= '<tr><td>'.$r['fecha'].'</td><td>'.$r['total_pedido'].'€</td><td>'.$r['comision'].'€</td><td>'.$is_redimido.'</td></tr>';
				}
			}
		}
		$informe.='Total referidos (total de visitas a través de tus enlaces): &nbsp; '.$leads.' <br>';
		$informe.='Número de ventas: &nbsp; '.$num_ventas.'<br>';
		if(!$total_ventas) $total_ventas = 0;

		$informe.='Total vendido:  &nbsp; '.$total_ventas.'€<br>';
		$informe.='Comisión actual: &nbsp; 30%<br>';
		if(!$pendiente) $pendiente=0;
		$informe.='<br>Pendiente de cobrar: &nbsp; '.$pendiente.'€<br><br><br>';								
		$informe.= '<table  id="informe_afiliados" style="width:70%;margin:auto;text-align:center;">';
		$informe.= '<tr><td>Fecha</td><td>Total</td><td>Tu parte</td><td>¿Cobrado?</td></tr>';		
		if(!$rows) $rows = '<tr><td colspan="4" style="text-align:Center"> Todavía no ha habido ningún pedido con tu código </td></tr>';
		$informe.=$rows;
		$informe.='</table>';		
		if(!$pendiente) $pendiente = 0;

	}else{ 
		$informe = 'Ha habido un error consultando los afiliados';	
	}


}


$novedades =  mysql_query("select * from _novedad order by id_novedad DESC");	
$aux = Array();
$i=0;
while($a = mysql_fetch_array($novedades)){
	$i++; if($i>10) break;
	$aux[] =$a;
}
$novedades = $aux;



?>
<?php get_header(); ?>
<!--Start Top Section -->

<style>
	.bloque{
		margin-left:45px;	
		margin-top:25px;
	}
	.bloque div{
		margin-left:45px;	
		margin-top:20px;	
	}

	table tr{
		line-height:30px;	
		min-height:30px;
	}

	table.descargas tr, table.novedades tr{
		line-height:120%;
	}
	table.novedades tr{
		line-height:170%;	
	}



	table.descargas tr td, table.novedades tr td{
		padding:6px 0px 6px 0px;	
		min-height:initial;	
		text-align:left;
	}



	#informe_afiliados td{
		padding:5px;
		border: 1px solid #999;
	}
	h3{
		font-weight: bold;	
	}
</style>
<!-- End TopSection -->
<div class="clear"></div>
<div class="subpagecontent">
	<div class="clear"></div>
	<div class="container_12" style="padding-bottom:0px;margin-bottom:20px">

		<div class="featuredbg subfeaturedbg">
			<div class="breadcrumbs">
				<div class="grid_12">
					<?php if ( function_exists('yoast_breadcrumb') ) {
	yoast_breadcrumb('<p>','</p>');
} else { ?>
					<p>
						<?php bloginfo('description'); ?>
					</p>
					<?php }?>
					<!--Breadcrumbs -->
				</div>
			</div>
			<!--		    <div class="subfeaturetitle">
<div class="grid_12">
<h2>
<?php wp_title("",true); ?>
</h2>
<!--Page Title-- >
</div>
</div>
<div class="featuredtop"></div>-->
		</div>


		<div style="float:right">
			<a href="#a" id="logout_ajax">Desconectar y salir de tu cuenta</a>
		</div>	
		<div class="grid_12">
			<div class="">        	    
				<h1>Hola <?=$display_name?>, este es tu espacio</h1>
				Aquí tienes acceso directo a tus descargas, cursos, pedidos y tu cuenta de usuario
				<?if($es_autor){?>
				<div style="width:50%;padding-bottom:20px;text-align:center;margin-top:40px;margin-bottom:10px" class="caja_gris">
					Accede a tu espacio de autor para gestionar tus descargas <br /> y acceder a los informes de ventas
					<br />
					<br />
					<a href="/espacio-autor" class="large dark button">Espacio autor</a>
				</div>	
				<?}?>	
				<?if($es_vip){?>
				<div style="width:50%;padding-bottom:20px;text-align:center;margin-top:40px;margin-bottom:10px" class="caja_gris">
					Entra en la comunidad VIP para acceder a las descargas, foros y demás
					<br />
					<br />
					<a href="/comunidad_vip" class="large dark button">Comunidad VIP</a>
				</div>	
				<?}?>						



				<!--
<div style="width:50%;padding-bottom:20px;text-align:center;margin-top:40px;margin-bottom:10px" class="caja_gris">
<h2>Aviso 2 de octubre 2014</h2>
<div style="text-align:left">
Hoy, 2 de octubre,  hemos cambiado de servidor y estamos teniendo un pequeño problemas con las descargas.
<br />	        	    			<br />
Estamos en ello y debería estar solucionado en las próximas horas.
<br />	        	    			<br />
Disculpa las molestias, seguimos trabajando para que todo funcione perfecto
<br />	        	    			<br />
Si necesitas contactar con nosotros escríbenos a contact@refractionproductions.com o a través del <a href="/contacto">formulario de contacto</a>.
<br />	        	    			<br />		        	    			
Gracias por tu comprensión,
<br />	        	    			<br />		        	    					        	    			
Carlos, Refraction Productions
</div>	
</div>	-->


			</div>

			<br /><br /><br />
			<div style="clear:both"> </div>
		</div>
		<div style="clear:both"> </div>
	</div>	           

	<div class="container_12" style="padding-bottom:0px;margin-bottom:60px">	           
		<div>




			<div style="float:left;width:48%;border-right:1px solid #ccc">			  				
				<h2>Tus descargas</h2>
				<div class="bloque subForm">

					<h3>Gratuitas</h3>
					<table class="descargas" style="margin-left:25px">
						<tr>
							<td style="width:336px"><a href="/espacio-usuario-descarga?descarga=1&id_descarga=9" target="_BLANK">DEMO Radio Bombs Vol.2</a></td>
							<td><a href="/espacio-usuario-descarga?descarga=1&id_descarga=9" target="_BLANK"><img src="/wp-content/images/download-green-50.png" style="max-width:25px"></a></td>
						</tr>
						<tr>
							<td><a href="/espacio-usuario-descarga?descarga=1&id_descarga=35" target="_BLANK">Free Sample Pack - 500Mb material gratuito </a> &nbsp; &nbsp; &nbsp; &nbsp; </td>
							<td><a href="/espacio-usuario-descarga?descarga=1&id_descarga=35" target="_BLANK"><img src="/wp-content/images/download-green-50.png" style="max-width:25px"></a></td>
						</tr>
					</table>	
					<br /><br />
					<h3>Librerías Loops, Samples, Cursos...</h3>												
					<table class="descargas" style="margin-left:25px;font-size:18px;margin-right:20px;line-height:100%">												
						<?if(!$articulos){?><!--<li> Todavía no hay ninguna descarga</li>--><?}else{?>			
						<?foreach($articulos as $d){?>
						<?if(in_array($d['id_articulo'],$descargas_ids)){?>
						<tr>
							<td><a href="http://refractionproductions.com/espacio-usuario-descarga?descarga=1&id_descarga=<?=$d['id_articulo']?>" target="_BLANK"><?=$d['nombre']?></a>   &nbsp; &nbsp; &nbsp; &nbsp;</td>
							<td><a href="http://refractionproductions.com/espacio-usuario-descarga?descarga=1&id_descarga=<?=$d['id_articulo']?>" target="_BLANK"><img src="/wp-content/images/download-green-50.png" style="max-width:25px"></a></td>
						</tr>
						<?}else{?>
						<tr>
							<td style="width:336px"><a href="http://refractionproductions.com/lib/<?=$d['url']?>" target="_BLANK"><?=$d['nombre']?></a> <br /> <small style="color:#888">(<?=$d['estilos']?>)</small> &nbsp; &nbsp; &nbsp; &nbsp;</td>
							<td><a href="http://refractionproductions.com/lib/<?=$d['url']?>" target="_BLANK"><img src="/wp-content/images/download-red-50.png" style="max-width:25px;opacity:0.5"></a></td>
						</tr>														
						<?}?>
						<?}?>			
						<?}?>
						<tr>
							<td style="">&nbsp;</td>
							<td></td>
						</tr>	
					</table>							


				</div>







				<br />	
				<br />
				<br />																					
				<!--<h2>Tus cursos</h2>
<div class="bloque subForm">
<ul style="font-size:18px">
<?if(false && $cursos){?>



<?}?>
<li style="margin-top:30px">Próximamente</li>									<!-- ARRIBA -- >
</ul>								
</div>		-->		           


			</div>
			<div style="float:left;width:40%;padding-left:4%;border-left:1px solid #ccc">
				<!--					           <h2>Novedades</h2>	           
<div style="margin-left:20px">
<h3><a href="http://refractionproductions.com/cursos-y-formacion-conectado/">Nuevos videos de formación, un adelanto de la nueva sección</a></h3>				           
<h3><a href="http://refractionproductions.com/radio-bombs-vol-2/">Radio Bombs II ya disponible</a></h3>
<h3><a href="http://refractionproductions.com/colabora-y-gana-dinero-en-refraction-productions/">Gana dinero con Refraction colaborando con contenidos</a></h3>				           
</div>-->
				<h2 style="">Últimas novedades</h2>	
				<div style="text-align:justify;margin-top:25px;margin-left:20px;margin-right:20px">		
					<table class="novedades">
						<?foreach($novedades as $n){?>
						<tr>
							<td style="width:100px">
								<?=$n['fecha_txt'];?>
							</td>
							<td>
								<?=$n['novedad'];?>
							</td>
						</tr>
						<?}?>					
					</table>		
				</div>						           
				<br />

				<br />
				<br />	    	

			</div>					
			<div style="clear:both"> </div>					
			<br />	           


		</div>
		<div style="clear:both"> </div>
	</div>	
	
	
	
	
	
	
	
	<!-- BLOQUE -->			
	
	
	
	
	
	
	
	<div class="container_12" style="margin-bottom:60px">
		<div class="grid_12">		       	           

			<h2>Afiliados - Gana dinero recomendándonos</h2>	 
			<div style="" class="bloque">
				<?if($afiliado){?>          
				<h3>Información del programa de afiliados</h3>		           
				<div style="text-align:center;">
					<a href="/afiliados" target="_BLANK" style="font-size:18px">Toda la info sobre comisiones y condiciones en este enlace</a>
				</div>
				<h3>Informe</h3>
				<div><?=$informe?>
				</div>
				<br /> 
				<br />
				<br />		           		         		           
				<h3>Datos de tu cuenta</h3>
				<div style="">
					<?$codigo = $afiliado['codigo'];?>
					<?$url = 'http://refractionproductions.com/radio-bombs-vol-1-libreria-de-loops-sonidos-hits/?aff='.$codigo;?>
					Tu código de afiliado es <strong style="font-weight:bold"><?=$codigo?></strong><br /><br />
					Los enlaces que hagas a Refraction deben terminar en  <span style="font-size:120%;font-weight:bold;color:#5A51C0">"?aff=<?=$codigo?>"</span>. Por ejemplo:<br /> <br />
					&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
					<a href="<?=$url?>" target="_BLANK"><?='http://refractionproductions.com/radio-bombs-vol-1-libreria-de-loops-sonidos-hits/<span style="font-size:120%;font-weight:bold;color:#5A51C0">?aff='.$codigo.'</span>'?></a><br />			
					<br />
					<br />				
					<div class="subForm" style="width:80%;margin:auto;margin-left:0px;">
						<form action="" method="POST" id="actualizar_afiliado_pago">
							<?if(!$afiliado['pago'] || !$afiliado['info_pago']){?>
							Atención! la información de pago está incompleta, la necesitarás para recibir los pagos<br /><br />
							<?}else{?>
							A continuación puedes ver tu información de pago y modificarla<br /><br />
							<?}?>
							<table style="margin:auto">
								<tr><td>Método pago &nbsp; &nbsp; &nbsp; </td><td>
									<select name="afiliado_pago" id="afiliado_select_pago" onChange="if($(this).val()=='Paypal'){ $('#info_pago_text').html('Correo paypal'); }else{ $('#info_pago_text').html('Num cuenta bancaria (IBAN) &nbsp; &nbsp;') } ">
										<option value="<?=$afiliado['pago']?>"> <?=$afiliado['pago']?></option>
										<option value="Paypal">Paypal</option>
										<option value="Cuenta bancaria">Cuenta bancaria</option>
									</select>
									</td></tr>
								<tr><td><span id="info_pago_text"><?if($afiliado['pago']=='Paypal'){echo 'Correo paypal&nbsp;&nbsp;';}else{ echo 'Num cuenta bancaria (IBAN) &nbsp;';};?></span> </td><td><input name="afiliado_info_pago" type="text" placeholder="Correo paypal o número cuenta bancaria (IBAN)" value="<?=$afiliado['info_pago']?>"></td></tr>
							</table>
							<div style="text-align:center">
								<input type="submit" value="Guardar preferencias" class="dark button large" onClick="$('#guardando_info_afiliado').show();$('#actualizar_afiliado_pago').submit();$(this).hide();;return true;"/>
								<img src='/wp-content/themes/ellipsis-nueva/images/ajax-loader.gif' style="display:none" id="guardando_info_afiliado">
							</div>	
						</form>
					</div>


					<?}else{?>

					<div class="bloque subForm" style="width:80%;margin:auto">
						El programa de afiliados te permite ganar dinero envíandonos visitas. Cuando te das de alta generamos un enlace único para ti que rastrea las visitas que nos mandas. Puedes colocar
						ese enlace en tu página o compartirlo en tus perfiles.
						<br />													<br />
						A partir de ese momento, cuando las visitas que nos envíes compren alguno de los productos de Refraction, te llevarás una comisión de esas compras, que actualmente es de un 30% (Por ejemplo, de la venta de Radio Bombs VOL.1 te llevarías 6€).
						<br />								<br />
						<div style="text-align:center;">
							<a href="/afiliados" target="_BLANK" style="font-size:18px">Toda de la info aquí</a>
						</div>
						<br />								<br />
						Y para darte de alta puedes hacerlo aquí:
						<br />								<br />
						<div style="text-align:center">
							<br />
							<a href="/espacio-usuario?alta_afiliado=1" id="" class="large dark button">Alta afiliado</a>
						</div>

						<div style="clear:both"> </div>

					</div>	           
					<div style="clear:both"> </div>
					<?}?>
				</div>


				<h3>Material</h3>
				<div>
					Todo el material para promocionar se encuentra en la página de afiliados.<br />
					<br /> 
					<div style="text-align:center;margin-left:-105px"><a href="/afiliados" class="dark medium button">Material promocional afiliados</a></div>
				</div>
				<br />
				<br />
				<br />



				<br />	           
				<br />	
			</div>	  
			<div style="clear:both"> </div>

		</div>
		<div style="clear:both"> </div>
	</div>	
	
	
	
	
	<!-- BLOQUE -->			
	
	
	<div class="container_12" style="padding-top:50px;margin-bottom:60px">
		<div class="grid_12">				

			<div style="float:left;width:50%">
				<h2>Tus pedidos</h2>
				<div class="bloque subForm">
					<?if(!$pedidos){?> Todavía no has realizado ningún pedido<?}else{?>
					<ul style="font-size:18px">
						<?$last_id = 0;?>
						<?foreach($pedidos as $p){?>
						<??>

						<li>

							Pedido <?=$p['id_pedido']?> - <?=date( 'd/m/Y ', strtotime( $p['fecha'] ));?>. <?if($p['pagado']){?>Pagado<?}else{?>No pagado<?}?>. Total:  <?=$p['total']?>
							<ul style="margin-left:20px">
								<?foreach($p['lineas'] as $l){?>
								<li><?=$l['articulo']?> - <?=$l['total_linea']?></li>											
								<?}?>
							</ul>
							<?if(!$p['pagado']){?> 
							&nbsp; &nbsp;<a href="#a" class="pagar_pedido" id="pagar_pedido_<?=$p['id_pedido']?>">Pagar</a> &nbsp; &nbsp; &nbsp;  <a href="#a" class="anular_pedido" id="anular_pedido_<?=$p['id_pedido']?>">Anular</a>
							<div id="info_pago_<?=$p['id_pedido']?>">

							</div>
							<?}?> 
							<br />										<br />
						</li>
						<?}?>
					</ul>
					<?}?>
				</div> 
			</div>
			<div style="float:left;width:45%;padding-left:2%;border-left:1px solid #ccc">				

				<h2>Tus datos de usuario</h2>	           
				<div class="bloque subForm" id="datos_usuario">
					<table>									
						<tr><td>Nombre</td><td><?=$display_name?></td></tr>
						<tr><td>Correo</td><td><?=$email_user?></td></tr>									
						<tr><td> Contraseña &nbsp;&nbsp;&nbsp;&nbsp;</td><td>*********</td></tr>
					</table>			
					<br />										<br />								
					<div style="text-align:center">										
						<input type="button" class="button dark medium" value="Editar datos" onClick="$('#datos_usuario').hide();$('#datos_usuario_editar').show();"/>
					</div>	
				</div>
				<div class="bloque subForm" id="datos_usuario_editar" style="display:none">
					<table>
						<tr><td>Nombre</td><td><input type="text" value="<?=$display_name?>" id="editar_nombre"></td></tr>
						<!--<tr><td>Login</td><td><input type="text"></td></tr>-->
						<tr><td>Correo</td><td><input type="text" value="<?=$email_user?>" id="editar_correo" readonly ></td></tr>
						<tr><td>Nueva Contraseña &nbsp;&nbsp;</td><td><input type="password" placeholder="******" id="editar_pass"></td></tr>
						<tr><td>Repite Contraseña &nbsp;&nbsp;</td><td><input type="password" placeholder="******" id="editar_pass2"></td></tr>		
					</table>

					<br />										<br />																		
					<div style="text-align:center">
						Escribe la contraseña solo si quieres cambiarla<br /><br /> 
						<input type="button" id="editar_datos_usuario" class="button dark medium" value="Guardar"/>
					</div>
				</div>

			</div>
		</div>
		<div style="clear:both"> </div>
	</div>
	
		
			
			
			
			
<!-- BLOQUE -->			
				
						
	<div class="container_12" style="padding-top:20px">
		<div class="grid_12">	

			<h2>Contacto</h2>
			<div class="bloque subForm">
				Mientras lo activamos <a href="/contacto">accede al area de contacto</a> o escríbenos a <a href="mailto:contact@refractionproductions.com">contact@refractionproductions.com</a>
				<br />											<br />
				También puedes llamarnos al (+34) 646 309 473 si tienes cualquier duda o pregunta.
			</div>	 

			<br />										          

		</div>
		<div style="clear:both"> </div>
	</div>
	
	
	
	
	
	
	<div class="clear"></div>

</div>

<script>

	$(document).ready(function(){		
		$('#editar_datos_usuario').click(function(){

			params = {}
			params.nombre = $('#editar_nombre').val();
			params.correo = $('#editar_correo').val();		
			params.pass = $('#editar_pass').val();		
			params.pass2 = $('#editar_pass2').val();						

			$('.warning_reg').remove();	
			$('input[type=text]').css('border','');			

			if(!params.nombre || params.nombre.length<3){
				$('#editar_nombre').css('border','2px solid red');	
				error += 'El nombre es incorrecto';				
			}
			if(!params.correo || !validate_email(params.correo)){
				$('#editar_correo').css('border','2px solid red');	
				error += 'El correo es incorrecto';				
			}			

			/*email_marketing = $('#email_marketing').prop('checked');
if(email_marketing) email_marketing = 1;
else email_marketing=0*/
			if(params.pass && params.pass<3){
				$('#editar_pass').css('border','2px solid red');	
				error += 'El pass es incorrecto';				
			}
			if(params.pass && (!params.pass2 || params.pass2<3 || params.pass2!=params.pass)){
				$('#editar_pass2').css('border','2px solid red');	
				error += 'Las contraseñas no coinciden';				
				alert('Las contraseñas no coinciden');
			}

			anchor = $(this);
			$(this).html('guardando...');


			callback = function(){
				alert('Se ha guardado correctamente');
				anchor.html('recargando la página...');
				window.location.reload();
			}
			callback_false = function(error){
				anchor.after("<div class='warning_reg' style='color:red;margin-top:20px;text-align:center'>"+error+"</div>");			
				alert('No se pudo guardar');
				anchor.html('Guardar');
				//				window.location.reload();				
			}

			app.post_wp('editar_datos_usuario_ajax',params,callback,callback_false);	
		});

		$('.anular_pedido').click(function(){
			id_pedido = $(this).attr('id').replace('anular_pedido_','');
			params = {}
			$(this).html('Anulando...');
			params.id_pedido = id_pedido;
			anchor = $(this);
			callback = function(){			
				anchor.parent().remove();
			}
			callback_false = function(){
				alert('No se pudo anular el pedido. Inténtalo de e nuevo por favor');		
				anchor.html('Anular');
			}

			app.post_wp('anular_pedido',params,callback,callback_false);			

		});

		$('.pagar_pedido').click(function(){
			id_pedido = $(this).attr('id').replace('pagar_pedido_','');
			str = '<br /><br />';
			str+= 'Puedes pagar el pedido por transferencia bancaria o paypal <br /><br />';
			str+= "-Para realizar el pago por transferencia bancaria, puedes hacerlo al número de cuenta 3058 2171 74 2720004437  (Cajamar) indicando tu nombre. Cuando tengas el recibo nos lo envias y así no esperamos a que llegue la cantidad a nuestra cuenta para activar tu descarga <br /><br />" 
			str+= "-O para realizar el pago paypal simplemente haz click en el siguiente enlace -> <a href='#a' class='pagar_paypal' id='pagar_paypal_"+id_pedido+"' style='text-decoration:underline'>Pagar por Paypal</a>";		
			$('#info_pago_'+id_pedido).html(str);
		});

		$('.pagar_paypal').live('click',function(){

			id_pedido = $(this).attr('id').replace('pagar_paypal_','');
			params = {}
			$(this).html('Conectando con paypal...');
			params.id_pedido = id_pedido;
			anchor = $(this);
			callback = function(pedido){			

				//rellenar datos y enviar a paypal per ipn 

				var amount =  pedido.total;
				var item_number = pedido.id_pedido
				var custom = pedido.codigo;
				$('#amount').val(amount);
				$('#item_number').val(item_number);					
				$('#custom').val(custom);										
				$('#paypal_form').submit(); 
			}
			callback_false = function(){
				alert('No se pudo pagar el pedido. Inténtalo de e nuevo por favor');		
				anchor.html('Pagar');
			}

			app.post_wp('pagar_pedido_paypal',params,callback,callback_false);			

		});

		$('#logout_ajax').click(function(){
			$(this).html('desconectando...'); 
			params = {}
			callback = function(){
				alert('Has salido de tu cuenta. Gracias por tu visita');
				$(this).html('recargando la página...');
				window.location.reload();
			}
			callback_false = function(){
				alert('No se pudo desconectar');
				window.location.reload();				
			}

			app.post_wp('desconectar',params,callback,callback_false);		
		});

	});

</script>
<script type='text/javascript' src='//refractionproductions.com/wp-content/themes/ellipsis-nueva/js/app.js?ver=3.5'></script>



<?php get_footer(); ?>