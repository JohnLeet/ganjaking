/**
 * @preserve
 * @name REVOLUTION 6.0.0 EXLPLODING ARTICLE EFFECTS ADDON
 * @version 2.1.4 (Date: 01-12-2020)
 * @author ThemePunch
 */

(function( $ ) {
	//'use strict';	
	// TRANSLATABLE CONTENT
	var bricks = revslider_explodinglayers_addon.bricks,	
		addon = {},
		slug = "revslider-explodinglayers-addon";
			
	// INITIALISE THE ADDON
	RVS.DOC.on(slug+'_init',function() {	
		
		// FIRST TIME INITIALISED
		if (!addon.initialised && RVS.SLIDER.settings.addOns[slug].enable) {
			
			// INIT LISTENERS
			initListeners();

			// EXTEND ANIMATION LIBRARY
			extendLayerAnimations();

			// ADD HOOKS
			addHooks();

			// CREATE CONTAINERS				
			RVS.F.addOnContainer.create({slug: slug, icon:"blur_on", title:bricks.explodinglayers, alias:bricks.explodinglayers, slider:false, slide:false, layer:false});								
			
			// PICK THE CONTAINERS WE NEED			
			addon.forms = {};				
			createLayerSettingsFields();	
			initHelp();
			addon.initialised = true;
		}

		// UDPATE FIELDS ID ENABLE
		if (RVS.SLIDER.settings.addOns[slug].enable) {								
			//Show Hide Areas
			updateAllCurrentLayer();
			
			// show help definitions
			if(typeof HelpGuide !== 'undefined') HelpGuide.activate('explodinglayers_addon'); 
			
		} else {
			// DISABLE THINGS
			showHideFrameSettings("show");
			unSetExplodeTabSelected();	

			// hide help definitions
			if(typeof HelpGuide !== 'undefined') HelpGuide.deactivate('explodinglayers_addon'); 
			
		}				
	});

			
	// INITIALISE typewriter LISTENERS
	function initListeners() {

		
		// RVS.DOC.on('slideAmountUpdated.explodinglayers',function() {});	
		
		var doc = RVS.DOC.on('click','.explodinglayers-icon',function() {
			$('#explodinglayers_shape').val(this.dataset.icon).trigger("change");
		});
		
		// UPDATE DUE BACKUP/RESTORE
		// RVS.DOC.on('SceneUpdatedAfterRestore.explodinglayers',function() {});

		// LAYER SELECTED
		doc.on('selectLayersDone.explodinglayers',function() {
			updateLayerStructure(RVS.selLayers[0]);
		});

		//EXPLODE ENABLED/DISABLED ON A FRAME
		doc.on('explodeOptionUpdate',function(e,p) {
			if (p===undefined || p.layerid===undefined) return;	
			if (RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].explodinglayers !==undefined && RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].explodinglayers.use) {
				//OPEN BACKUP GROUP AND UPDATE ALL
				RVS.F.openBackupGroup({id:"SetExplodeLayers",txt:"Explode Layer On",icon:"bubble_chart"});					
				var pre = "#frame#.";				
				RVS.F.updateLayerObj({path:pre+'mask.use',val:false});
				RVS.F.updateLayerObj({path:pre+'chars.use',val:false});
				RVS.F.updateLayerObj({path:pre+'words.use',val:false});
				RVS.F.updateLayerObj({path:pre+'filter.use',val:false});
				RVS.F.updateLayerObj({path:pre+'sfx.effect',val:'none'});
				RVS.F.closeBackupGroup({id:"SetExplodeLayers"});					
			}
			RVS.F.updateFrameOptionsVisual();				
		});

		//ADD SVG FROM OBJECT LIBRARY
		doc.on('click','#add_explodinglayer_svg',function() {
			RVS.F.openObjectLibrary({types:["svgs"],filter:"all", selected:["svgs"], success:{icon:"inserSVGIntoExplodingLayers"}});
		});

		//GET THE OBJECT LIBRARY SVG BACK, UPDATE VALUES
		doc.on('inserSVGIntoExplodingLayers',function(e,p) {
			if (p!==undefined && p.path!==undefined)
				$('#explodinglayers_shape').val(p.path).change();
		});

		// GET NEW CONTENT OF CHANGED SHAPE
		doc.on('drawSelectedSVGExplodeLayers',function(a,b) {
			if (b!==undefined && b.val!==undefined) drawSelectedSVGExplodeLayers(b.val);				
		});
		
	}

	// DRAW THE SELECTED SHAPE AFTER NEEDS
	function drawSelectedSVGExplodeLayers(_) {
		if (addon.svgs[_]===undefined && _!=="circle") {
			addon.forms.selectedshape.attr('d',_);
			addon.forms.selectedshapewrap[0].style.opacity = 1;
		}
		else {
			addon.forms.selectedshapewrap[0].style.opacity = 0;
		}
	}

	// HIDE / SHOW ALL FRAME SETTINGS
	function showHideFrameSettings(_) {

		for(var i in RVS.V.frameLevels) {
			if(RVS.V.frameLevels.hasOwnProperty(i)) {
				
				if(RVS.V.frameLevels[RVS.V.frameLevels[i]] && RVS.V.frameLevels[RVS.V.frameLevels[i]].length) {
					RVS.V.frameLevels[RVS.V.frameLevels[i]][0].style.pointerEvents = _ === 'show' ? '' : 'none';	
				}
				//RVS.V.frameLevels[RVS.V.frameLevels[i]][0].style.pointerEvents = (_==="show" ? "" : "none");
			}
		}
		
		document.getElementById('layerbasic_ts_wrapbrtn').style.pointerEvents = (_==="show" ? "" : "none");
		_!=="show" ? document.getElementById('layerbasic_ts_wrapbrtn').className +=" notinuse" : document.getElementById('layerbasic_ts_wrapbrtn').className ="ts_wrapbrtn";
		
	}
	
	function updateColorInput() {
	
		if(typeof RVS.S.clickedLayer !== 'undefined' && typeof RVS.S.keyFrame !== 'undefined') {
			if (RVS.SLIDER[RVS.S.slideId].layers[RVS.S.clickedLayer]!==undefined) {
				var frame = RVS.SLIDER[RVS.S.slideId].layers[RVS.S.clickedLayer].timeline.frames[RVS.S.keyFrame];
				if(frame.hasOwnProperty('explodinglayers')) {				
					var color = frame.explodinglayers.color;
					$('#expllay_fr_color').attr('data-color', color).rsColorPicker('refresh');
					
				}
			}
		
		}
	
	}

	// ADD JQUERY HOOKS	
	function addHooks() {	

		// Change Mode if Layer Selected
		RVS.JHOOKS.updateFrameOptionsVisual.push(function() {	
		
			if (RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].explodinglayers!==undefined && RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].explodinglayers.use) {
				RVS.V.frameLevels.explodinglayers[0].className="ts_wrapbrtn";
				RVS.V.frameLevels.explodinglayers[0].style.display = "inline-block";
				// HIDE UNNEDED TABS
				showHideFrameSettings("hide");
				// SELECT EXPLODE IF NOT SELECTED YET
				setExplodeTabSelected();
				updateColorInput();
				drawSelectedSVGExplodeLayers(RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].explodinglayers.type);
			} else

			if (RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].explodinglayers !==undefined && (RVS.S.keyFrame==="frame_1" || RVS.S.keyFrame==="frame_999")) {
				RVS.V.frameLevels.explodinglayers[0].style.display = "inline-block";
				RVS.V.frameLevels.explodinglayers[0].className="ts_wrapbrtn notinuse";
				showHideFrameSettings("show");
			}

			else {
				RVS.V.frameLevels.explodinglayers[0].style.display = "none";
				showHideFrameSettings("show");
				unSetExplodeTabSelected();
			}
			
		});

		//EXTEND FRAME AT BUILDING IT
		RVS.JHOOKS.defaultFrame.push(function(frame) {				
			frame.explodinglayers = getExplodeFrame();
			return frame;
		});

		// UPDATE LAYER ANIMATION FRAME EXPLODE ATTRIBUTE
		RVS.JHOOKS.changeLayerAnimation.push(function(_) {
							
			if (!(_.direction==="in" && RVS.LIB.LAYERANIMS[_.direction][_.group].transitions[_.transition].frame_1!==undefined && RVS.LIB.LAYERANIMS[_.direction][_.group].transitions[_.transition].frame_1.explodinglayers!==undefined && RVS.LIB.LAYERANIMS[_.direction][_.group].transitions[_.transition].frame_1.explodinglayers.use===true) &&
				!(_.direction==="out" && RVS.LIB.LAYERANIMS[_.direction][_.group].transitions[_.transition].frame_999!==undefined && RVS.LIB.LAYERANIMS[_.direction][_.group].transitions[_.transition].frame_999.explodinglayers!==undefined && RVS.LIB.LAYERANIMS[_.direction][_.group].transitions[_.transition].frame_999.explodinglayers.use===true))
				RVS.F.updateLayerObj({path:'#frame#.explodinglayers.use',val:false});
		});
	}



	//EXTEND LAYER ANIMATIONS
	function extendLayerAnimations() {
		
		var defSettings = getExplodeFrame();
		defSettings.use = true;
		
		RVS.F.extendLayerAnimationLists({direction:"in", handle:"explodelayers", preset:{group:"Exploding Layers", 
			transitions: {	
				"explodebasic":{
					
					name:"Exploding Layers", 
					frame_1:{
						
						timeline: {speed: 1000, ease: 'Power2.easeOut'}, 
						transform: {opacity: 1},
						chars: {use: false}, 
						words: {use: false}, 
						lines: {use: false}, 
						mask: {use: false}, 
						filter: {use: false}, 
						explodinglayers: $.extend({}, defSettings)
						
					}
				}					
		}}});

		RVS.F.extendLayerAnimationLists({direction:"out", handle:"explodelayers", preset:{group:"Exploding Layers", 
			transitions: {	
				"explodebasic":{
					
					name:"Exploding Layers", 
					frame_999:{
						
						timeline: {speed: 1000, ease: 'Power2.easeOut'}, 
						transform: {opacity: 1},
						chars: {use: false}, 
						words: {use: false}, 
						lines: {use: false}, 
						mask: {use: false},  
						filter: {use: false},
						explodinglayers: $.extend({}, defSettings)
						
					}	
				}
			
		}}});
	}


	// UPDATE ALL CURRENT LAYERS STRUCTURE
	function updateAllCurrentLayer() { for (var i in RVS.L) if (RVS.L[i].timeline!==undefined) updateLayerStructure(i);}
	
	function getExplodeFrame() {
		return {
			use: false,
			color: '#000000',
			density: '1',
			direction: 'left',
			padding: '150',
			power: '2',
			randomsize: false,
			randomspeed: false,
			sync: false,
			size: '5',
			speed: '1',
			style: 'fill',
			type: 'circle'
		};
	}

	function updateLayerStructure(uid) {			
		if (RVS.L[uid]==undefined) return;			
		RVS.L[uid].timeline.frames.frame_1.explodinglayers = RVS.L[uid].timeline.frames.frame_1.explodinglayers === undefined ? getExplodeFrame() : jQuery.extend(true, getExplodeFrame(), RVS.L[uid].timeline.frames.frame_1.explodinglayers);
		RVS.L[uid].timeline.frames.frame_999.explodinglayers = RVS.L[uid].timeline.frames.frame_999.explodinglayers === undefined ? getExplodeFrame() : jQuery.extend(true, getExplodeFrame(), RVS.L[uid].timeline.frames.frame_999.explodinglayers);
	}

	//Set EXPLODE FRAME TAB Selected
	function setExplodeTabSelected() {
		if (RVS.S.frameTrgt!=="explodinglayers") {
			$('.transtarget_selector').removeClass("selected");
			$(RVS.V.frameLevels.explodinglayers).find('.transtarget_selector').addClass("selected");
			$('.group_transsettings').hide();
			$('#explode_transsettings').show();
			RVS.S.frameTrgt ="explodinglayers";
		}
	}

	function unSetExplodeTabSelected() {
		if (RVS.S.frameTrgt==="explodinglayers") {
			$('.transtarget_selector').removeClass("selected");
			$('#layerbasic_ts_wrapbrtn').find('.transtarget_selector').addClass("selected");
			$('.group_transsettings').hide();
			$('#layer_transsettings').show();
			RVS.S.frameTrgt ="layer";
		}
	}
				


	// CREATE INPUT FIELDS
	function createLayerSettingsFields() {
		if (!addon.slidersettings) {
							
			addon.svgs = {'rectangle' : 'M4 4h16v16H4z', 
						'triangle' : 'M12 4L4 20L20 20z', 
						'polygon' : 'M5 4 L17 4 L22 12 L17 20 L8 20 L3 12 L8 4 Z', 
						'star' : 'M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z',
						'heart_1' : 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z',		
						'star_2' : 'M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm4.24 16L12 15.45 7.77 18l1.12-4.81-3.73-3.23 4.92-.42L12 5l1.92 4.53 4.92.42-3.73 3.23L16.23 18z',			
						'settings' : 'M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z',			
						'arrow_1' : 'M4 18l8.5-6L4 6v12zm9-12v12l8.5-6L13 6z',
						'bullseye' : 'M12 2C6.49 2 2 6.49 2 12s4.49 10 10 10 10-4.49 10-10S17.51 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3-8c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3z',
						'plus_1' : 'M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4V7zm-1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z',
						'triangle_2' : 'M12 7.77L18.39 18H5.61L12 7.77M12 4L2 20h20L12 4z',
						'smilie' : 'M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z',
						'star_3' : 'M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z',
						'heart_2' : 'M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z',
						'plus_2' : 'M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z',
						'close' : 'M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z',
						'arrow_2' : 'M22 12l-4-4v3H3v2h15v3z',
						'dollar' : 'M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z',
						'sun_1' : 'M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.79 1.42-1.41zM4 10.5H1v2h3v-2zm9-9.95h-2V3.5h2V.55zm7.45 3.91l-1.41-1.41-1.79 1.79 1.41 1.41 1.79-1.79zm-3.21 13.7l1.79 1.8 1.41-1.41-1.8-1.79-1.4 1.4zM20 10.5v2h3v-2h-3zm-8-5c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm-1 16.95h2V19.5h-2v2.95zm-7.45-3.91l1.41 1.41 1.79-1.8-1.41-1.41-1.79 1.8z',
						'sun_2' : 'M7 11H1v2h6v-2zm2.17-3.24L7.05 5.64 5.64 7.05l2.12 2.12 1.41-1.41zM13 1h-2v6h2V1zm5.36 6.05l-1.41-1.41-2.12 2.12 1.41 1.41 2.12-2.12zM17 11v2h6v-2h-6zm-5-2c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zm2.83 7.24l2.12 2.12 1.41-1.41-2.12-2.12-1.41 1.41zm-9.19.71l1.41 1.41 2.12-2.12-1.41-1.41-2.12 2.12zM11 23h2v-6h-2v6z',
						'snowflake' : 'M22 11h-4.17l3.24-3.24-1.41-1.42L15 11h-2V9l4.66-4.66-1.42-1.41L13 6.17V2h-2v4.17L7.76 2.93 6.34 4.34 11 9v2H9L4.34 6.34 2.93 7.76 6.17 11H2v2h4.17l-3.24 3.24 1.41 1.42L9 13h2v2l-4.66 4.66 1.42 1.41L11 17.83V22h2v-4.17l3.24 3.24 1.42-1.41L13 15v-2h2l4.66 4.66 1.41-1.42L17.83 13H22z',
						'party' : 'M4.59 6.89c.7-.71 1.4-1.35 1.71-1.22.5.2 0 1.03-.3 1.52-.25.42-2.86 3.89-2.86 6.31 0 1.28.48 2.34 1.34 2.98.75.56 1.74.73 2.64.46 1.07-.31 1.95-1.4 3.06-2.77 1.21-1.49 2.83-3.44 4.08-3.44 1.63 0 1.65 1.01 1.76 1.79-3.78.64-5.38 3.67-5.38 5.37 0 1.7 1.44 3.09 3.21 3.09 1.63 0 4.29-1.33 4.69-6.1H21v-2.5h-2.47c-.15-1.65-1.09-4.2-4.03-4.2-2.25 0-4.18 1.91-4.94 2.84-.58.73-2.06 2.48-2.29 2.72-.25.3-.68.84-1.11.84-.45 0-.72-.83-.36-1.92.35-1.09 1.4-2.86 1.85-3.52.78-1.14 1.3-1.92 1.3-3.28C8.95 3.69 7.31 3 6.44 3 5.12 3 3.97 4 3.72 4.25c-.36.36-.66.66-.88.93l1.75 1.71zm9.29 11.66c-.31 0-.74-.26-.74-.72 0-.6.73-2.2 2.87-2.76-.3 2.69-1.43 3.48-2.13 3.48z',
						'flower_1' : 'M18.7 12.4c-.28-.16-.57-.29-.86-.4.29-.11.58-.24.86-.4 1.92-1.11 2.99-3.12 3-5.19-1.79-1.03-4.07-1.11-6 0-.28.16-.54.35-.78.54.05-.31.08-.63.08-.95 0-2.22-1.21-4.15-3-5.19C10.21 1.85 9 3.78 9 6c0 .32.03.64.08.95-.24-.2-.5-.39-.78-.55-1.92-1.11-4.2-1.03-6 0 0 2.07 1.07 4.08 3 5.19.28.16.57.29.86.4-.29.11-.58.24-.86.4-1.92 1.11-2.99 3.12-3 5.19 1.79 1.03 4.07 1.11 6 0 .28-.16.54-.35.78-.54-.05.32-.08.64-.08.96 0 2.22 1.21 4.15 3 5.19 1.79-1.04 3-2.97 3-5.19 0-.32-.03-.64-.08-.95.24.2.5.38.78.54 1.92 1.11 4.2 1.03 6 0-.01-2.07-1.08-4.08-3-5.19zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z',
						'flower_2' : 'M12 22c4.97 0 9-4.03 9-9-4.97 0-9 4.03-9 9zM5.6 10.25c0 1.38 1.12 2.5 2.5 2.5.53 0 1.01-.16 1.42-.44l-.02.19c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5l-.02-.19c.4.28.89.44 1.42.44 1.38 0 2.5-1.12 2.5-2.5 0-1-.59-1.85-1.43-2.25.84-.4 1.43-1.25 1.43-2.25 0-1.38-1.12-2.5-2.5-2.5-.53 0-1.01.16-1.42.44l.02-.19C14.5 2.12 13.38 1 12 1S9.5 2.12 9.5 3.5l.02.19c-.4-.28-.89-.44-1.42-.44-1.38 0-2.5 1.12-2.5 2.5 0 1 .59 1.85 1.43 2.25-.84.4-1.43 1.25-1.43 2.25zM12 5.5c1.38 0 2.5 1.12 2.5 2.5s-1.12 2.5-2.5 2.5S9.5 9.38 9.5 8s1.12-2.5 2.5-2.5zM3 13c0 4.97 4.03 9 9 9 0-4.97-4.03-9-9-9z',
						'fire' : 'M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z',
						'pizza' : 'M12 2C8.43 2 5.23 3.54 3.01 6L12 22l8.99-16C18.78 3.55 15.57 2 12 2zM7 7c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm5 8c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z'
				};		

			$('#layer_maintranssettings_wrap')[0].innerHTML+='<div style="display:inline-block" class="show_on_frame_1 show_on_frame_999"><div id="explode_ts_wrapbrtn" class="ts_wrapbrtn"><div data-showtrans="#explode_transsettings" data-frametarget="explodinglayers" class="transtarget_selector">Explode</div></div></div>';

			var _h = '';
			
			_h = '<div id="explode_transsettings" class="group_transsettings enable_on_frame_1 enable_on_frame_999" style="display:none">';			
			_h += '		<label_a>Explode Layer</label_a><input type="checkbox" data-showhide="#_explode_layer_settings" data-showhidedep="true" class="layerinput easyinit callEvent" data-evt="explodeOptionUpdate" data-r="#frame#.explodinglayers.use" />';								
			_h += '	<div id="_explode_layer_settings">';
			_h += '		<div id="explodinglayers_iconselector_wrap">';
			_h += '			<span data-icon="circle" class="explodinglayers-icon explayi_circle" data-helpkey="#frame#.explodinglayers.type"><span class="explodinglayers-circle"></span></span>';
			for (var i in addon.svgs) {
				if(!addon.svgs.hasOwnProperty(i)) continue;
				_h += '		<span class="explodinglayers-icon explayi_'+i+'" data-icon="'+i+'" data-helpkey="#frame#.explodinglayers.type"><svg xmlns="http://www.w3.og/2000/svg" viewBox="0 0 24 24"><path fill="#777c80" d="'+addon.svgs[i]+'"></path></svg></span>';				
			}
			_h += '		<div class="div15"></div>';
			_h += '			<div>';
			_h += '				<span id="olib_exla_selection" class="explodinglayers-icon" data-icon="rectangle"><svg xmlns="http://www.w3.og/2000/svg" viewBox="0 0 24 24"><path id="exla_sel_path" fill="#777c80" d="M4 4h16v16H4z"></path></svg></span>';
			_h += '				<div class="basic_action_button longbutton callEventButton rightbutton" id="add_explodinglayer_svg"><i class="material-icons">camera_enhance</i>'+bricks.objlibrary+'</div>';
			_h += '				<div class="tp-clearfix"></div>';
			_h += '			</div>';				
			_h += '			<input  style="display:none!important" class="layerinput easyinit callEvent" data-evt="drawSelectedSVGExplodeLayers" data-select=".explayi_*val*" data-unselect=".explodinglayers-icon" id="explodinglayers_shape" data-r="#frame#.explodinglayers.type" type="text">';
			_h += '		</div>';
			_h += '		<div class="div25"></div>';
			_h += '		<label_a>'+bricks.particlecolor+'</label_a><input type="text" data-editing="'+bricks.particlecolor+'" name="explode_layers_frame_color" id="expllay_fr_color" data-visible="true" class="my-explodelayers-color-field layerinput easyinit" data-r="#frame#.explodinglayers.color" value="#fff">';
			_h += '		<label_a>'+bricks.particlestyle+'</label_a><select id="el_part_style"  class="layerinput tos2 nosearchbox easyinit" data-r="#frame#.explodinglayers.style">';
			_h += '			<option value="fill">'+bricks.fill+'</option>';
			_h += '			<option value="stroke">'+bricks.stroke+'</option>';
			_h += '		</select><linebreak></linebreak>';
			_h += '		<row class="direktrow">';
			_h += '			<onelong><label_a>'+bricks.particlesize+'</label_a><input class="layerinput valueduekeyboard easyinit" data-numeric="true" data-allowed="" data-r="#frame#.explodinglayers.size" data-min="0" data-max="5000" type="text"></onelong>';
			_h += '			<oneshort><i class="label_mi material-icons">shuffle</i><input type="checkbox" class="layerinput easyinit" data-r="#frame#.explodinglayers.randomsize" value="on"></oneshort>';
			_h += '		</row>';
			_h += '		<label_a>'+bricks.direction+'</label_a><select id="el_part_expldirection"  class="layerinput tos2 nosearchbox easyinit" data-r="#frame#.explodinglayers.direction">';
			_h += '			<option value="top">'+bricks.top+'</option>';
			_h += '			<option value="right">'+bricks.right+'</option>';
			_h += '			<option value="bottom">'+bricks.bottom+'</option>';
			_h += '			<option value="left">'+bricks.left+'</option>';
			_h += '		</select><linebreak></linebreak>';
			_h += '		<row class="direktrow">';
			_h += '			<onelong><label_a>'+bricks.antigravity+'</label_a><input class="layerinput valueduekeyboard easyinit" data-numeric="true" data-allowed="" data-r="#frame#.explodinglayers.speed" data-min="0" data-max="5000" type="text"></onelong>';
			_h += '			<oneshort><i class="label_mi material-icons">shuffle</i><input type="checkbox" class="layerinput easyinit" data-r="#frame#.explodinglayers.randomspeed" value="on"></oneshort>';
			_h += '		</row>';
			_h += '		<row class="direktrow">';
			_h += '			<onelong><i class="label_mi material-icons">line_style</i><input class="layerinput valueduekeyboard easyinit" data-numeric="true" data-allowed="" data-r="#frame#.explodinglayers.density" data-min="0" data-max="5000" type="text"></onelong>';
			_h += '			<oneshort><label_icon class="ui_fit"></label_icon><input class="layerinput valueduekeyboard easyinit" data-numeric="true" data-allowed="" data-r="#frame#.explodinglayers.power" data-min="0" data-max="5000" type="text"></oneshort>';
			_h += '		</row>';
			_h += '		<label_a>'+bricks.padding+'</label_a><input class="layerinput valueduekeyboard easyinit" data-numeric="true" data-allowed="px" data-r="#frame#.explodinglayers.padding" data-min="0" data-max="5000" type="text"><br />';
			_h += '		<div class="show_on_frame_1"><label_a>'+bricks.synchelper+'</label_a><input type="checkbox" class="layerinput easyinit" data-r="#frame#.explodinglayers.sync" value="on"></oneshort></div>';
			_h +='	</div>';
			_h +='</div>';
							


			addon.forms.framesettings = $(_h);
			$('#form_animation_sframes_innerwrap').append(addon.forms.framesettings);
			addon.forms.selectedshape = $('#exla_sel_path');
			addon.forms.selectedshapewrap = $('#olib_exla_selection');
			RVS.V.frameLevels.explodinglayers = $('#explode_ts_wrapbrtn');

			
			addon.forms.framesettings.find('.tos2.nosearchbox').ddTP({
				
				placeholder:"Select From List"
			});				
			
			RVS.F.initTpColorBoxes('#explode_transsettings .my-explodelayers-color-field');
							
			RVS.F.initOnOff();
			

			
		}
	}
	
	function initHelp() {
		
		// only add on-demand if the AddOn plugin is activated from inside the editor
		// otherwise if the AddOn plugin is already activated, the help definitions will get added when the help guide is officially used (via php filter)
		if(typeof HelpGuide !== 'undefined' && revslider_explodinglayers_addon.hasOwnProperty('help')) {
		
			var obj = {slug: 'explodinglayers_addon'};
			$.extend(true, obj, revslider_explodinglayers_addon.help);
			HelpGuide.add(obj);
			
		}
	
	}


})( jQuery );