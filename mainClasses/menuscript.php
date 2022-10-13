<?php
$_SESSION['modulos']=array();
for($inc=0;$inc!=count($menu_cpa);$inc++){
	if($menu_cpa[$inc]['estado']=='on'){
		print("<li><a");
		if(strlen($menu_cpa[$inc]['link'])>0){
			print(' href="index.php?'.$menu_cpa[$inc]['link'].'"');
		}
		print("><span><span>".$menu_cpa[$inc]['nombre']."</span></span></a>");
		if($menu_cpa[$inc]['submenu']=='si'){
			$query="select titulo, path, permisos, id_modulo_sis, tipoLink, params from sis_modulos where menu=".$inc." and estado='on' order by orden asc";
			$pdo=$classMain->conexion_db($options);
			if(is_object($pdo)){
				try { 
					$consulta = $pdo->query($query);
					if($consulta){
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC); 
						if (count($filas)> 0) {
							print "<ul>";
							foreach($filas as $key => $r){
								$_SESSION['modulos'][$r['id_modulo_sis']]=$r;
								if($classMain->secure($r['permisos'],$_SESSION["login"]['permisos'])){
									if($r['tipoLink']==0){
										print '<li onclick="core.go2href(\'index.php?'.$r['params'].'\');">'.utf8_encode($r['titulo']).'</li>';
									} 
									if($r['tipoLink']==1){
										print '<li onclick="core.go2href(\'index.php?m='.$r['id_modulo_sis'].'\');">'.utf8_encode($r['titulo']).'</li>';
									} 
									if($r['tipoLink']==2){
										//openBoxMulti(w,h,d,r,m,script,accion,id,title,idelement,position)
										$params=explode(',',$r['params']);
										print "<li onclick=\"core.openBoxMulti({
											width:".$params[0].",
											height:".$params[1].",
											draggable:".$params[2].",
											resizable:".$params[3].",
											modal:".$params[4].",
											module:'".$r['path']."',
											method:'".$params[5]."',
											view:'".$params[6]."',
											id:".$r['id_modulo_sis'].",
											title:'".$r['titulo']."'});\">".utf8_encode($r['titulo'])."</li>";
									}
								}
							} 
							print "</ul>";
						}
					} 
				} catch (PDOException $e) { 
					echo 'Falló la conexión: '.$e->getMessage();
				}
			}
		}
		print("</li>");
	}
} 
?>