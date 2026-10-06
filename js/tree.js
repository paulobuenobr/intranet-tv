var TREE_TPL = {
	'target'  : '_self',	// name of the frame links will be opened in

  'icon_e'  : 'imagens/empty.gif', // empty image
  'icon_l'  : 'imagens/line.gif',  // vertical line

  'icon_sn'  : 'imagens/servidores_estrutura_nodo.gif',
  'icon_s0'  : 'imagens/servidores_estrutura_servidor.gif',

  'icon_i1'  : 'imagens/servidores_estrutura_interface_1.gif',
  'icon_i2'  : 'imagens/servidores_estrutura_interface_2.gif',
  'icon_i3'  : 'imagens/servidores_estrutura_interface_3.gif',
  'icon_i4'  : 'imagens/servidores_estrutura_interface_4.gif',
  'icon_i5'  : 'imagens/servidores_estrutura_interface_5.gif',

  'icon_32' : 'imagens/base.gif',   // root leaf icon normal
  'icon_36' : 'imagens/base.gif',   // root leaf icon selected

  'icon_48' : 'imagens/base.gif',   // root icon normal
  'icon_52' : 'imagens/base.gif',   // root icon selected
  'icon_56' : 'imagens/base.gif',   // root icon opened
  'icon_60' : 'imagens/base.gif',   // root icon selected

  'icon_16' : 'imagens/folder.gif', // node icon normal
  'icon_20' : 'imagens/folderopen.gif', // node icon selected
  'icon_24' : 'imagens/folderopen.gif', // node icon opened
  'icon_28' : 'imagens/folderopen.gif', // node icon selected opened

  'icon_0'  : 'imagens/page.gif', // leaf icon normal
  'icon_4'  : 'imagens/page.gif', // leaf icon selected

  'icon_2'  : 'imagens/joinbottom.gif', // junction for leaf
  'icon_3'  : 'imagens/join.gif',       // junction for last leaf
  'icon_18' : 'imagens/plusbottom.gif', // junction for closed node
  'icon_19' : 'imagens/plus.gif',       // junctioin for last closed node
  'icon_26' : 'imagens/minusbottom.gif',// junction for opened node
  'icon_27' : 'imagens/minus.gif'       // junctioin for last opended node
};

function tree (a_items, a_template) {

	this.a_tpl      = a_template;
	this.a_config   = a_items;
	this.o_root     = this;
	this.a_index    = [];
	this.o_selected = null;
	this.n_depth    = -1;

	var o_icone = new Image(),
	o_iconl = new Image();
	o_icone.src = a_template['icon_e'];
	o_iconl.src = a_template['icon_l'];
	a_template['im_e'] = o_icone;
	a_template['im_l'] = o_iconl;
	for (var i = 0; i < 64; i++)
		if (a_template['icon_' + i]) {
			var o_icon = new Image();
			a_template['im_' + i] = o_icon;
			o_icon.src = a_template['icon_' + i];
		}

	this.toggle = function (n_id) {	var o_item = this.a_index[n_id]; o_item.open(o_item.b_opened) };
	this.select = function (n_id) { return this.a_index[n_id].select(); };
	this.mout   = function (n_id) { this.a_index[n_id].upstatus(true) };
	this.mover  = function (n_id) { this.a_index[n_id].upstatus() };

	this.a_children = [];
	for (var i = 0; i < a_items.length; i++)
		new tree_item(this, i);

	this.n_id = trees.length;
	trees[this.n_id] = this;

	for (var i = 0; i < this.a_children.length; i++) {
		document.write(this.a_children[i].init());
		this.a_children[i].open();
	}
}

function tree_item (o_parent, n_order) {

	this.n_depth  = o_parent.n_depth + 1;
	this.a_config = o_parent.a_config[n_order + (this.n_depth ? 3 : 0)];
	if (!this.a_config) return;

	this.o_root    = o_parent.o_root;
	this.o_parent  = o_parent;
	this.n_order   = n_order;
	this.b_opened  = !this.n_depth;

	this.n_id = this.o_root.a_index.length;
	this.o_root.a_index[this.n_id] = this;
	o_parent.a_children[n_order] = this;

	this.a_children = [];
	for (var i = 0; i < this.a_config.length - 2; i++)
		new tree_item(this, i);

	this.get_icon = item_get_icon;
	this.open     = item_open;
	this.select   = item_select;
	this.init     = item_init;
	this.upstatus = item_upstatus;
	this.is_last  = function () { return this.n_order == this.o_parent.a_children.length - 1 };
}

function item_open (b_close) {
	var o_idiv = get_element('i_div' + this.o_root.n_id + '_' + this.n_id);
	if (!o_idiv) return;

	if (!o_idiv.innerHTML) {
		var a_children = [];
		for (var i = 0; i < this.a_children.length; i++)
			a_children[i]= this.a_children[i].init();
		o_idiv.innerHTML = a_children.join('');
	}
	o_idiv.style.display = (b_close ? 'none' : 'block');

	this.b_opened = !b_close;
	var o_jicon = document.images['j_img' + this.o_root.n_id + '_' + this.n_id],
		o_iicon = document.images['i_img' + this.o_root.n_id + '_' + this.n_id];
	if (o_jicon) o_jicon.src = this.get_icon(true);
	if (o_iicon) o_iicon.src = this.get_icon();
	this.upstatus();
}

function item_select (b_deselect) {
	if (!b_deselect) {
		var o_olditem = this.o_root.o_selected;
		this.o_root.o_selected = this;
		if (o_olditem) o_olditem.select(true);
	}
	var o_iicon = document.images['i_img' + this.o_root.n_id + '_' + this.n_id];
	if (o_iicon) o_iicon.src = this.get_icon();
//	get_element('i_txt' + this.o_root.n_id + '_' + this.n_id).style.fontWeight = b_deselect ? 'normal' : 'bold';

	this.upstatus();
	return Boolean(this.a_config[1]);
}

function item_upstatus (b_clear) {
	window.setTimeout('window.status=""', 10);
}

function item_init () {
	var a_offset = [],
		o_current_item = this.o_parent;
	for (var i = this.n_depth; i > 1; i--) {
		a_offset[i] = '<img src="' + this.o_root.a_tpl[o_current_item.is_last() ? 'icon_e' : 'icon_l'] + '" border="0" align="absbottom">';
		o_current_item = o_current_item.o_parent;
	}
	return '<table cellpadding="0" cellspacing="0" border="0"><tr><td nowrap>' + (this.n_depth ? a_offset.join('') + (this.a_children.length
		? '<a href="javascript: trees[' + this.o_root.n_id + '].toggle(' + this.n_id + ')" onmouseover="trees[' + this.o_root.n_id + '].mover(' + this.n_id + ')" onmouseout="trees[' + this.o_root.n_id + '].mout(' + this.n_id + ')"><img src="' + this.get_icon(true) + '" border="0" align="absbottom" name="j_img' + this.o_root.n_id + '_' + this.n_id + '"></a>'
		: '<img src="' + this.get_icon(true) + '" border="0" align="absbottom">') : '')
		+ '<a href="' + this.a_config[1] + '" target="' + this.o_root.a_tpl['target'] + '" onclick="return trees[' + this.o_root.n_id + '].select(' + this.n_id + ')" ondblclick="trees[' + this.o_root.n_id + '].toggle(' + this.n_id + ')" onmouseover="trees[' + this.o_root.n_id + '].mover(' + this.n_id + ')" onmouseout="trees[' + this.o_root.n_id + '].mout(' + this.n_id + ')" class="atree" id="i_txt' + this.o_root.n_id + '_' + this.n_id + '"><img src="' + this.get_icon() + '" border="0" align="absbottom" name="i_img' + this.o_root.n_id + '_' + this.n_id + '" class="atree">&nbsp;' + this.a_config[0] + '</a></td></tr></table>' + (this.a_children.length ? '<div id="i_div' + this.o_root.n_id + '_' + this.n_id + '" style="display:none"></div>' : '');
}

function item_get_icon (b_junction) {
	if (b_junction)
		return this.o_root.a_tpl['icon_' + ((this.n_depth ? 0 : 32) + (this.a_children.length ? 16 : 0) + (this.a_children.length && this.b_opened ? 8 : 0) + (!b_junction && this.o_root.o_selected == this ? 4 : 0) + (b_junction ? 2 : 0) + (b_junction && this.is_last() ? 1 : 0))]; else
		return this.o_root.a_tpl['icon_' + this.a_config[2]];
}

var trees = [];
get_element = document.all ?
	function (s_id) { return document.all[s_id] } :
	function (s_id) { return document.getElementById(s_id) };