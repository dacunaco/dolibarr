<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/product/canvas/service/actions_card_service.class.php
 *	\ingroup    service
 *	\brief      Fichier de la classe des services par defaut
 */
include_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');

/**
 *	\class      ProductService
 *	\brief      Class with controller methods for product canvas
 */
class ActionsCardService extends Product
{
	var $db;
    var $targetmodule;
    var $canvas;
    var $card;

    //! Template container
	var $tpl = array();

	// List of fiels for action=list
	var $field_list =array();


	/**
	 *    Constructor
	 *
     *    @param   DoliDB	$DB             Handler acces base de donnees
     *    @param   string	$targetmodule   Name of directory of module where canvas is stored
     *    @param   string	$canvas         Name of canvas
     *    @param   string	$card           Name of tab (sub-canvas)
	 */
	function ActionsCardIndividual($DB,$targetmodule,$canvas,$card)
	{
		$this->db 				= $DB;
		$this->targetmodule     = $targetmodule;
        $this->canvas           = $canvas;
        $this->card             = $card;

		$this->module 			= "service";
		$this->name 			= "service";
		$this->definition 		= "Services canvas";
		$this->fieldListName	= "product_service";
		$this->next_prev_filter = "canvas='service'";
	}

    /**
     *  Return the title of card
     */
	private function getTitle()
	{
		global $langs;

		return $langs->trans("Products");
	}

	/**
	 *    Assign custom values for canvas (for example into this->tpl to be used by templates)
	 *
	 *    @param      action     Type of action
	 */
	function assign_values($action)
	{
		global $conf,$langs,$user;
		global $html, $formproduct;

		// canvas
		$this->tpl['canvas'] = $this->canvas;

		// id
		$this->tpl['id'] = $this->id;

		// Ref
		$this->tpl['ref'] = $this->ref;

		// Label
		$this->tpl['label'] = $this->libelle;

		// Description
		$this->tpl['description'] = nl2br($this->description);

		// Statut
		$this->tpl['status'] = $this->getLibStatut(2);

		// Note
		$this->tpl['note'] = nl2br($this->note);

		if ($action == 'create')
		{
			// Price
			$this->tpl['price'] = $this->price;
			$this->tpl['price_min'] = $this->price_min;
			$this->tpl['price_base_type'] = $html->load_PriceBaseType($this->price_base_type, "price_base_type");

			// VAT
			$this->tpl['tva_tx'] = $html->load_tva("tva_tx",-1,$mysoc,'');
		}

		if ($action == 'create' || $action == 'edit')
		{
			// Status
			$statutarray=array('1' => $langs->trans("OnSell"), '0' => $langs->trans("NotOnSell"));
			$this->tpl['status'] = $html->selectarray('statut',$statutarray,$this->status);

			//To Buy
			$statutarray=array('1' => $langs->trans("Yes"), '0' => $langs->trans("No"));
			$this->tpl['tobuy'] = $html->selectarray('tobuy',$statutarray,$this->status_buy);

            $this->tpl['description'] = $this->description;
            $this->tpl['note'] = $this->note;
		}

		if ($action == 'view')
		{
			// Ref
			$this->tpl['ref'] = $html->showrefnav($this,'ref','',1,'ref');

			// Accountancy buy code
			$this->tpl['accountancyBuyCodeKey'] = $html->editfieldkey("ProductAccountancyBuyCode",'productaccountancycodesell',$this->accountancy_code_sell,'id',$this->id,$user->rights->produit->creer);
			$this->tpl['accountancyBuyCodeVal'] = $html->editfieldval("ProductAccountancyBuyCode",'productaccountancycodesell',$this->accountancy_code_sell,'id',$this->id,$user->rights->produit->creer);

			// Accountancy sell code
			$this->tpl['accountancySellCodeKey'] = $html->editfieldkey("ProductAccountancySellCode",'productaccountancycodebuy',$this->accountancy_code_buy,'id',$this->id,$user->rights->produit->creer);
			$this->tpl['accountancySellCodeVal'] = $html->editfieldval("ProductAccountancySellCode",'productaccountancycodebuy',$this->accountancy_code_buy,'id',$this->id,$user->rights->produit->creer);
		}
		
		$this->tpl['finished'] = $this->object->finished;
		$this->tpl['ref'] = $this->object->ref;
		$this->tpl['label'] = $this->object->label;
		$this->tpl['id'] = $this->object->id;
		$this->tpl['type'] = $this->object->type;
		$this->tpl['note'] = $this->object->note;
		$this->tpl['seuil_stock_alerte'] = $this->object->seuil_stock_alerte;

		// Duration
		$this->tpl['duration_value'] = $this->object->duration_value;

		if ($action == 'create')
		{
			// Title
			$this->tpl['title'] = $langs->trans("NewService");
		}

		if ($action == 'edit')
		{
			$this->tpl['title'] = $langs->trans('Modify').' '.$langs->trans('Service').' : '.$this->object->ref;
		}

		if ($action == 'create' || $action == 'edit')
		{
    		// Status
    		$statutarray=array('1' => $langs->trans("OnSell"), '0' => $langs->trans("NotOnSell"));
    		$this->tpl['status'] = $html->selectarray('statut',$statutarray,$_POST["statut"]);

    		$statutarray=array('1' => $langs->trans("ProductStatusOnBuy"), '0' => $langs->trans("ProductStatusNotOnBuy"));
    		$this->tpl['status_buy'] = $html->selectarray('statut_buy',$statutarray,$_POST["statut_buy"]);

		    // Duration unit
			// TODO creer fonction
			$duration_unit = '<input name="duration_unit" type="radio" value="h"'.($this->object->duration_unit=='h'?' checked':'').'>'.$langs->trans("Hour");
			$duration_unit.= '&nbsp; ';
			$duration_unit.= '<input name="duration_unit" type="radio" value="d"'.($this->object->duration_unit=='d'?' checked':'').'>'.$langs->trans("Day");
			$duration_unit.= '&nbsp; ';
			$duration_unit.= '<input name="duration_unit" type="radio" value="w"'.($this->object->duration_unit=='w'?' checked':'').'>'.$langs->trans("Week");
			$duration_unit.= '&nbsp; ';
			$duration_unit.= '<input name="duration_unit" type="radio" value="m"'.($this->object->duration_unit=='m'?' checked':'').'>'.$langs->trans("Month");
			$duration_unit.= '&nbsp; ';
			$duration_unit.= '<input name="duration_unit" type="radio" value="y"'.($this->object->duration_unit=='y'?' checked':'').'>'.$langs->trans("Year");
			$this->tpl['duration_unit'] = $duration_unit;
		}

		if ($action == 'view')
		{
    		$head=product_prepare_head($this->object, $user);
    		$titre=$langs->trans("CardProduct".$this->object->type);
    		$picto=($this->object->type==1?'service':'product');
    		$this->tpl['fiche_head']=dol_get_fiche_head($head, 'card', $titre, 0, $picto);

    		// Status
    		$this->tpl['status'] = $this->object->getLibStatut(2,0);
    		$this->tpl['status_buy'] = $this->object->getLibStatut(2,1);

		    // Photo
			$this->tpl['nblignes'] = 4;
			if ($this->object->is_photo_available($conf->service->dir_output))
			{
				$this->tpl['photos'] = $this->object->show_photos($conf->service->dir_output,1,1,0,0,0,80);
			}

			// Duration
			if ($this->object->duration_value > 1)
			{
				$dur=array("h"=>$langs->trans("Hours"),"d"=>$langs->trans("Days"),"w"=>$langs->trans("Weeks"),"m"=>$langs->trans("Months"),"y"=>$langs->trans("Years"));
			}
			else if ($this->object->duration_value > 0)
			{
				$dur=array("h"=>$langs->trans("Hour"),"d"=>$langs->trans("Day"),"w"=>$langs->trans("Week"),"m"=>$langs->trans("Month"),"y"=>$langs->trans("Year"));
			}
			$this->tpl['duration_unit'] = $langs->trans($dur[$this->object->duration_unit]);

			$this->tpl['fiche_end']=dol_get_fiche_end();
		}

		if ($action == 'list')
		{
	        $this->LoadListDatas($GLOBALS['limit'], $GLOBALS['offset'], $GLOBALS['sortfield'], $GLOBALS['sortorder']);
		}

	}


	/**
	 * 	Fetch field list
	 */
	private function getFieldList()
	{
		global $conf, $langs;

        $this->field_list = array();

		$sql = "SELECT rowid, name, alias, title, align, sort, search, enabled, rang";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_field_list";
		$sql.= " WHERE element = '".$this->fieldListName."'";
		$sql.= " AND entity = ".$conf->entity;
		$sql.= " ORDER BY rang ASC";

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);

			$i = 0;
			while ($i < $num)
			{
				$fieldlist = array();

				$obj = $this->db->fetch_object($resql);

				$fieldlist["id"]		= $obj->rowid;
				$fieldlist["name"]		= $obj->name;
				$fieldlist["alias"]		= $obj->alias;
				$fieldlist["title"]		= $langs->trans($obj->title);
				$fieldlist["align"]		= $obj->align;
				$fieldlist["sort"]		= $obj->sort;
				$fieldlist["search"]	= $obj->search;
				$fieldlist["enabled"]	= verifCond($obj->enabled);
				$fieldlist["order"]		= $obj->rang;

				array_push($this->field_list,$fieldlist);

				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($db,$sql);
		}
	}

	/**
	 * 	\brief	Fetch datas list
	 */
	function LoadListDatas($limit, $offset, $sortfield, $sortorder)
	{
		global $conf;

        $this->getFieldList();

		$sql = 'SELECT DISTINCT p.rowid, p.ref, p.label, p.barcode, p.price, p.price_ttc, p.price_base_type,';
		$sql.= ' p.fk_product_type, p.tms as datem,';
		$sql.= ' p.duration, p.tosell as statut, p.seuil_stock_alerte';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'product as p';
		// We'll need this table joined to the select in order to filter by categ
		if ($search_categ) $sql.= ", ".MAIN_DB_PREFIX."categorie_product as cp";
		if ($_GET["fourn_id"] > 0)
		{
			$fourn_id = $_GET["fourn_id"];
			$sql.= ", ".MAIN_DB_PREFIX."product_fournisseur as pf";
		}
		$sql.= " WHERE p.entity = ".$conf->entity;
		if ($search_categ) $sql.= " AND p.rowid = cp.fk_product";	// Join for the needed table to filter by categ
		if ($sall)
		{
			$sql.= " AND (p.ref like '%".$this->db->escape($sall)."%' OR p.label like '%".$this->db->escape($sall)."%' OR p.description like '%".$this->db->escape($sall)."%' OR p.note like '%".$this->db->escape($sall)."%')";
		}
		if ($sref)     $sql.= " AND p.ref like '%".$sref."%'";
		if ($sbarcode) $sql.= " AND p.barcode like '%".$sbarcode."%'";
		if ($snom)     $sql.= " AND p.label like '%".$this->db->escape($snom)."%'";
		if (isset($_GET["tosell"]) && dol_strlen($_GET["tosell"]) > 0)
		{
			$sql.= " AND p.tosell = ".$this->db->escape($_GET["tosell"]);
		}
		if (isset($_GET["canvas"]) && dol_strlen($_GET["canvas"]) > 0)
		{
			$sql.= " AND p.canvas = '".$this->db->escape($_GET["canvas"])."'";
		}
		if($catid)
		{
			$sql.= " AND cp.fk_categorie = ".$catid;
		}
		if ($fourn_id > 0)
		{
			$sql.= " AND p.rowid = pf.fk_product AND pf.fk_soc = ".$fourn_id;
		}
		// Insert categ filter
		if ($search_categ)
		{
			$sql .= " AND cp.fk_categorie = ".$this->db->escape($search_categ);
		}
		$sql.= $this->db->order($sortfield,$sortorder);
		$sql.= $this->db->plimit($limit + 1 ,$offset);

		$this->list_datas = array();

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);

			$i = 0;
			while ($i < min($num,$limit))
			{
				$datas = array();
				$obj = $this->db->fetch_object($resql);

				$datas["id"]        = $obj->rowid;
				$datas["ref"]       = $obj->ref;
				$datas["label"]     = $obj->label;
				$datas["barcode"]   = $obj->barcode;
				$datas["statut"]    = $obj->statut;

				array_push($this->list_datas,$datas);

				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			print $sql;
		}
	}

}

?>