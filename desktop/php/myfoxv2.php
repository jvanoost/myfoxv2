


<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'myfoxv2');
$eqLogics = eqLogic::byType('myfoxv2')
?>

<div class="row row-overflow">
  <div class="col-lg-2 col-md-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav nav-tabs">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un Systeme Myfox}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<li class="cursor li_eqLogic " data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity . '"><a class="eqLogicAction">' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
     </ul>
   </div>
 </div>

 <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
  <legend>{{Mes systemes Myfox}}
  </legend>
  <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
     <center>
      <i class="fa fa-plus-circle" style="font-size : 7em;color:#94ca02;"></i>
    </center>
    <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>Ajouter un systeme Myfox</center></span>
  </div>
  <?php
 foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/myfoxv2/doc/images/myfoxv2_icon.png" height="105"  />';
                    echo "</center>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
?>
</div>
</div>

    <div class="col-lg-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <form class="form-horizontal">
            <fieldset>
                <legend>{{Général}}</legend>
	 	<div class="alert alert-info">
		     {{Les paramètres Client Id et CLient Secret sont à récupérer sur 'https://api.myfox.me/' rubrique My Applications, Attention a ne pas faire d'erreur ici, au risque de bloquer pendant 1 heure l'accés à votre centrale depuis votre IP}}
	        </div>
                 <div class="form-group">
                    <label class="col-lg-2 control-label">{{Nom de l'équipement Myfox}}</label>
                    <div class="col-lg-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement Myfox}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label" >{{Objet parent}}</label>
                    <div class="col-lg-3">
                        <select class="eqLogicAttr form-control" data-l1key="object_id">
                            <option value="">{{Aucun}}</option>
                            <?php
                            foreach (JeeObject::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
				
				
				 <div class="form-group">
            <label class="col-sm-2 control-label">{{Catégorie}}</label>
            <div class="col-sm-10">
                <?php
foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
	echo '<label class="checkbox-inline">';
	echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
	echo '</label>';
}
?>

           </div>
		   </div>
               <div class="form-group">
         <label class="col-sm-2 control-label"></label>
         <div class="col-sm-8">
              <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>{{Activer}}</label>
              <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr " data-label-text="{{Visible}}" data-l1key="isVisible" checked/>{{Visible}}</label>
       </div>
   </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">{{Client Id}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="myfoxv2ClientId" placeholder="myfoxv2ClientId"/>
						{{Exemple}}: fdc0ab034bde5465af4192937a07a43e
                    </div>
                </div>
		<div class="form-group">
                    <label class="col-md-2 control-label">{{Client Secret}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="myfoxv2ClientSecret" placeholder="myfoxv2ClientSecret"/>
						{{Exemple}}: S2M3Be1iuBKFQlEg8Dz9Tq1bW8sbkFRf
                    </div>
                </div>
		<div class="form-group">
                    <label class="col-md-2 control-label">{{Username}}</label>
                    <div class="col-md-3">
                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="myfoxv2Username" placeholder="myfoxv2Username"/>
						{{Exemple}}: monadressemail@exemple.fr
                    </div>
                </div>
               <div class="form-group">
                    <label class="col-md-2 control-label">{{Password}}</label>
                    <div class="col-md-3">
                        <input type="password" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="myfoxv2Password" placeholder="myfoxv2Password"/>
						{{Exemple}}: s3cr3tPassw0rd
                    </div>
                </div>		
				
            </fieldset> 
        </form>

<legend>{{Commandes}}</legend>
<table id="table_cmd" class="table table-bordered table-condensed">
  <thead>
    <tr>
	  <th  >{{N°}}</th>
      <th >{{Nom}}</th>
	  <th >{{Options}}</th>
	  <th >{{Afficher}}</th>
	  <th >{{Parametres}}</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>



<form class="form-horizontal">
  <fieldset>
    <div class="form-actions">
      <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
      <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
    </div>
  </fieldset>
</form>

</div>
</div>

<?php include_file('desktop', 'myfoxv2', 'js', 'myfoxv2'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>