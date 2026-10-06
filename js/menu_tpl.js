var TMCE=[],TMCF=[],TMCG=[],TMCR=[],TMCT=[],TMCU=[],TMCV=[];

TMCE.TMBb=20; // height
TMCE.TMCK=false; // vertical
TMCE.TMCG=[0,1,0]; // table
TMCE.TMCM=100; // exop_delay
TMCE.TMCQ=250; // hide_delay

TMCT.TMCG='m0table'; // css : table
TMCT.TMCW=['m0moutoTop','m0mouto','m0mouto']; // css : outer
TMCT.TMCX='m0inner'; // css : inner

TMCF.TMBa=180; // width
TMCF.TMBb=20; // height
TMCF.TMCH=20; // block_top
TMCF.TMCI=-1; // block_left
TMCF.TMCK=true; // vertical
TMCF.TMCJ=1; // wise_pos
TMCF.TMCG=[0,1,0]; // table
TMCF.TMCQ=400; // hide_delay

TMCU.TMCG='m0table'; // css : table
TMCU.TMCW=['m0mouto2','m0movero2','m0movero2']; // css : outer
TMCU.TMCX=['m0inner2','m0innerOver2','m0innerOver2']; // css : inner

TMCG.TMBa=159; // width
TMCG.TMBb=20; // height
TMCG.TMCH=20; // block_top
TMCG.TMCI=-1; // block_left
TMCG.TMCK=true; // vertical
TMCG.TMCJ=1; // wise_pos
TMCG.TMCG=[0,1,0]; // table
TMCG.TMCQ=400; // hide_delay

TMCV.TMCG='m0table'; // css : table
TMCV.TMCW=['m0mouto2','m0movero2','m0movero2']; // css : outer
TMCV.TMCX=['m0inner2','m0innerOver2','m0innerOver2']; // css : inner

TMCE.TMCY=TMCT;
TMCF.TMCY=TMCU;
TMCG.TMCY=TMCV;

var MENU_POS = [TMCE, TMCF, TMCG];

var TMCF2=[];

TMCF2.TMBa=441; // width
TMCF2.TMBb=20; // height
TMCF2.TMCH=20; // block_top
TMCF2.TMCI=-1; // block_left
TMCF2.TMCK=true; // vertical
TMCF2.TMCJ=1; // wise_pos
TMCF2.TMCG=[0,1,0]; // table
TMCF2.TMCQ=400; // hide_delay
TMCF2.TMCY=TMCU;

var RAMAL_POS = [TMCE, TMCF2, TMCG];