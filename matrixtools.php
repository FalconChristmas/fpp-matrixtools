<?
$canvasWidth = 1000;
$canvasHeight = 400;
?>
<style>
.matrix-tool-top-panel {
	padding-bottom: 0px !important;
}

.matrix-tool-middle-panel {
	padding-bottom: 0px !important;
	padding-top: 0px !important;
}

.matrix-tool-bottom-panel {
	padding-top: 0px !important;
}

.red {
	background: #ff0000;
}

.green {
	background: #00ff00;
}

.blue {
	background: #0000ff;
}

.yellow {
	background: #ffff00;
}

.orange {
	background: #ff8800;
}

.white {
	background: #ffffff;
}

.black {
	background: #000000;
}

.colorButton {
	-moz-transition: border-color 250ms ease-in-out 0s;
	background-clip: padding-box;
	border: 2px solid rgba(0, 0, 0, 0.25);
	border-radius: 50% 50% 50% 50%;
	cursor: pointer;
	display: inline-block;
	height: 20px;
	margin: 1px 2px;
	width: 20px;
}

#currentColor {
    border: 2px solid #000000;
}

</style>

<script type="text/javascript">
var blockList = {};
var blockData = [];
var blockName = "Matrix1";

    if ( ! window.console ) console = { log: function(){} };

	var cellColors = {};
	var currentColor = '#ff0000';

	function blockState() {
		var state = $('#blockOnOffSwitch').val();
        $.ajax({
               url: "/api/overlays/model/" + blockName + "/state",
               method: 'PUT',
               contentType: "application/json",
               data: '{"State": ' + state + '}', // data as js object
               success: function() {}
               });
	}

	function autoFillChanged() {
		if ($('#AutoFill').is(':checked'))
			FillMatrix();
		else
			ClearMatrix();
	}

    function ShowColorPicker() {
		if ($('#ShowColorPicker').is(':checked')) {
            $('#colpicker').show();
        } else {
            $('#colpicker').hide();
        }
    }

	function refreshMatrix() {
		$('#mmcanvas').drawLayers();
	}

	function setColor(color) {
		if (color.substring(0,1) != '#')
			color = '#' + color;

        pluginSettings['color'] = color;
        SetPluginSetting('fpp-matrixtools', 'color', color, 0, 0);
        $('#currentColor').css('background-color', color);

		currentColor = color;
		$('#colpicker').colpickSetColor(color);

		if ($('#AutoFill').is(':checked'))
			FillMatrix();
	}

	function setColorsFromData() {
		cellColors = {};
		var width = blockList[blockName].width;

        if (useRLE) {
            var i = 0;
            for (var p = 0; p < blockData.length; p += 4) {
                var c = blockData[p];
                var r = blockData[p+1];
                var g = blockData[p+2];
                var b = blockData[p+3];

                for (var j = 0; j < c; j++, i++) {
                    var x = i % width;
                    var y = parseInt(i / width);
                    var key = x + "," + y;
			        cellColors[key] = '#' + $.colpick.rgbToHex({ r: r, g: g, b: b});
                }
            }
        } else {
            for (var p = 0; p < blockData.length; p += 3)
            {
                var x = p / 3 % width;
                var y = parseInt(p / 3 / width);
                var key = x + "," + y;
                cellColors[key] = '#' + $.colpick.rgbToHex({ r: blockData[p], g: blockData[p+1], b: blockData[p+2]});
            }
        }

		refreshMatrix();
	}

	function hexToRgb(hex) {
		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? {
			r: parseInt(result[1], 16),
			g: parseInt(result[2], 16),
			b: parseInt(result[3], 16)
		} : null;
	}

	function ClearMatrix() {
        $.get( "/api/overlays/model/" + blockName + "/clear", function(data) {
              });
		cellColors = {};
		refreshMatrix();
	}

	function selectBlock(name, save = true) {
        if (save) {
            pluginSettings['model'] = name;
            SetPluginSetting('fpp-matrixtools', 'model', name, 0, 0);
        }

		blockName = name;
		GetBlockData();
		InitCanvas();

		$('#blockOnOffSwitch').val(blockList[blockName].isActive);
	}

    var useRLE = true;
	function GetBlockData() {
        var path = "/data";
        if (useRLE)
            path += "/rle";

        $.get( "/api/overlays/model/" + blockName + path, function(data) {
            if ((useRLE) &&
                (!data.hasOwnProperty('rle') || !data.rle)) {
                useRLE = false;
                return;
            }

            blockData = data.data;
            setColorsFromData();
            if (!data.isLocked) {
                StopBlockDataTimer();
            }
        });
	}

	var blockDataTimer = null;
	function StartBlockDataTimer() {
        if (blockDataTimer != null) {
            clearInterval(blockDataTimer);
        }
		blockDataTimer = setInterval(function(){GetBlockData()}, useRLE ? 50 : 100);
	}

	function StopBlockDataTimer() {
		if (blockDataTimer != null) {
			clearInterval(blockDataTimer);
            blockDataTimer = null;
		}
	}

    function FontChanged() {
        var font = $('#fontList').val();
        pluginSettings['font'] = font;
        SetPluginSetting('fpp-matrixtools', 'font', font, 0, 0);
    }

	function GetFontList() {
        $.get( "/api/overlays/fonts", function(data) {
              $('#fontList option').remove();
              data.forEach( function (item, index) {
                  var key = item;
			      var text = key.replace(/[^-a-zA-Z0-9]/g, '');
                  var option = "<option value='" + key + "'";
                  if (pluginSettings['font'] == key)
                    option += ' selected';
                  option += ">" + text + "</option>";
                  $('#fontList').append(option);
              });
           });
	}

	function GetBlockList() {
        $.get( "/api/overlays/models", function(data){
              blockList = new Map();
              $('#blockList option').remove();
              blockName = "";
              data.forEach( function (item, index) {
                    if (blockName == "") {
                        blockName = item["Name"];
                    }
                    var key = item["Name"];
                    if (item.Orientation == 'vertical') {
                           item.height = item.ChannelCount / item.StrandsPerString / item.StringCount / 3;
                           item.width = item.ChannelCount / 3 / item.height;
                    } else {
                           item.width = item.ChannelCount / item.StrandsPerString / item.StringCount / 3;
                           item.height = item.ChannelCount / 3 / item.width;
                    }
                           
                    blockList[key] = item;

                    var option = "<option value='" + key + "'";
                    if (pluginSettings['model'] == key) {
                        option += ' selected';
                        blockName = key;
                    }
                    option += ">" + key + " (" + blockList[key].width + "x" + blockList[key].height + ")</option>";
                    $('#blockList').append(option);
              });
              selectBlock(blockName, false);
        });
	}

	function FillMatrix() {
		var rgb = hexToRgb(currentColor);
        $.ajax({
               url: "/api/overlays/model/" + blockName + "/fill",
               method: 'PUT',
               contentType: "application/json",
               data: JSON.stringify({RGB: [ rgb.r, rgb.g, rgb.b ]}),
               success: function() {}
               });
        
		if (currentColor == "#000000") {
			cellColors = {};
		} else {
			for (var x = 0; x < blockList[blockName].width; x++) {
				for (var y = 0; y < blockList[blockName].height; y++) {
					key = x + "," + y;
					cellColors[key] = currentColor;
				}
			}
		}
		refreshMatrix();
	}

	function ColorPixelUnderMouse(layer) {
		var x = Math.floor(layer.eventX / cellsize);

        if (x >= blockList[blockName].width)
            x = blockList[blockName].width - 1;

		var y = Math.floor(layer.eventY / cellsize);

        if (y >= blockList[blockName].height)
            y = blockList[blockName].height - 1;

		if (pluginSettings['LargePen'] == "1") {
			for (var xd = -1; xd <= 1; xd++) {
				for (var yd = -1; yd <= 1; yd++) {
					ColorPixel(x + xd, y + yd);
				}
			}
		} else {
			ColorPixel(x, y);
		}
	}

	function ColorPixel(x, y) {
		if ((x >= blockList[blockName].width) ||
			(y >= blockList[blockName].height) ||
			(x < 0) ||
			(y < 0))
			return;

		var key = x + "," + y;
		if (currentColor == "")
			currentColor = '#000000';
				
		if (currentColor == '#000000')
			delete cellColors[key];
		else
			cellColors[key] = currentColor;

		refreshMatrix();

		var rgb = hexToRgb(currentColor);
        $.ajax({
               url: "/api/overlays/model/" + blockName + "/pixel",
               method: 'PUT',
               contentType: "application/json",
               data: JSON.stringify({RGB: [ rgb.r, rgb.g, rgb.b ], X: x, Y: y}),
               success: function() {}
               });
    }

	function PlaceText() {
//		ClearMatrix();
		var msg = $('#inputText').val();
		var data = {
			Message: msg,
			Color: currentColor,
			Font: $('#fontList').val(),
			FontSize: parseInt($('#fontSize').val()),
			Position: $('#textPosition').val(),
			PixelsPerSecond: parseInt($('#scrollSpeed').val()),
            AntiAlias: $('#antiAliased').prop('checked'),
            AutoEnable: $('#autoEnable').prop('checked')
			};

		if ($('#ShowTextEffect').is(':checked'))
			StartBlockDataTimer();
        
        $.ajax({
               url: "/api/overlays/model/" + blockName + "/text",
               method: 'PUT',
               contentType: "application/json",
               data: JSON.stringify(data),
               success: function() {
                    GetBlockData();
               }
               });
	}

	var canvasWidth = <? echo $canvasWidth; ?>;
	var canvasHeight = <? echo $canvasHeight; ?>;
	var cellsize = 10;
	var halfCellSize = Math.floor(cellsize / 2);
	var quarterCellSize = Math.floor(halfCellSize / 2);
	var mouseDown = 0;

	function InitCanvas() {
        if ((blockList[blockName].width > 400) ||
            (blockList[blockName].height > 400)) {
            $('#mmcanvas').hide();
            $('#warning').html('Model too large to display.');
            $('#warning').show();
            return;
        } else {
            $('#warning').hide();
            $('#warning').html('');
            $('#mmcanvas').show();
        }

        canvasWidth = <? echo $canvasWidth; ?>;
        canvasHeight = <? echo $canvasHeight; ?>;

        canvasWidth = window.innerWidth - 150;
        if (canvasWidth < 500)
            canvasWidth = 500;

        canvasHeight = parseInt(canvasWidth / 1.7);

		if ((blockList[blockName].width > (canvasWidth / 10)) || (blockList[blockName].height > (canvasHeight / 10)))
			cellsize = 5;
        cellsize = 5;

        xsize = parseInt(canvasWidth / blockList[blockName].width);
        ysize = parseInt(canvasHeight / blockList[blockName].height);
        if (xsize < ysize)
            cellsize = xsize;
        else
            cellsize = ysize;
        if (cellsize > 20)
            cellsize = 20;

        halfCellSize = Math.floor(cellsize / 2);
        quarterCellSize = Math.floor(halfCellSize / 2);

		if (cellsize > 4)
			$('.showGridWrapper').show();
		else
			$('.showGridWrapper').hide();

		canvasWidth = blockList[blockName].width * cellsize;
		canvasHeight = blockList[blockName].height * cellsize;

        var ctx = $('#mmcanvas')[0].getContext('2d');
        ctx.canvas.width = canvasWidth;
        ctx.canvas.height = canvasHeight;

		$('#mmcanvas').removeLayers();
		$('#mmcanvas').clearCanvas();

		// Draw the Black background
		$('#mmcanvas').drawRect({
			fromCenter: false,
			layer: true,
			fillStyle: '#000',
			x: 0,
			y: 0,
			width: canvasWidth,
			height: canvasHeight,
			mousedown: function(layer) {
				mouseDown = 1;
			},
			mouseup: function(layer) {
				ColorPixelUnderMouse(layer);
				mouseDown = 0;
			},
			mousemove: function(layer) {
				if (mouseDown)
					ColorPixelUnderMouse(layer);
			},
		});

		// Draw the grid layer
		$('#mmcanvas').draw({
			layer: true,
			fn: function(ctx) {

				if ((pluginSettings['ShowGrid'] == "1") &&
					(cellsize > 4)) {
					for (var x = 0; x <= canvasWidth; x += cellsize) {
						ctx.beginPath();
						ctx.strokeStyle = '#555';
						ctx.lineWidth = 2;
						ctx.moveTo(x, 0);
						ctx.lineTo(x, canvasHeight);
						ctx.stroke();
					}

					for (var y = 0; y <= canvasHeight; y += cellsize) {
						ctx.beginPath();
						ctx.strokeStyle = '#555';
						ctx.lineWidth = 2;
						ctx.moveTo(0, y);
						ctx.lineTo(canvasWidth, y);
						ctx.stroke();
					}
				}
			}
		});

		// Draw the Pixel layer
		$('#mmcanvas').draw({
			layer: true,
			fn: function(ctx) {
				var keys = Object.keys(cellColors);
				for (var i in keys) {
					var key = keys[i];
					if (cellColors[key] != '#000000')
					{
						var coordinates = key.split(',');
						var x = parseInt(coordinates[0]);
						var y = parseInt(coordinates[1]);
						x = x * cellsize + 1;
						y = y * cellsize + 1;

						ctx.beginPath();
						if ((halfCellSize) && (quarterCellSize) && (pluginSettings['ShowRoundPixels'] == "1")) {
							ctx.arc(x + halfCellSize, y + halfCellSize, quarterCellSize, 0, 2 * Math.PI, false);
						} else {
							ctx.rect(x, y, cellsize - 2, cellsize - 2);
						}
						ctx.fillStyle = cellColors[key];
						ctx.fill();
						ctx.lineWidth = 1;
						ctx.strokeStyle = cellColors[key];
						ctx.stroke();
					}
				}
			}
		});

	}
</script>


<div class='fppTabs'>
	<div class='title'>Matrix Tools</div>
	<div id="matrixTabs">
		<ul>
			<li><a href="#tab-mmtext">Text</a></li>
			<li><a href="#tab-mmdraw">Draw</a></li>
<!--
			<li><a href="#tab-mmimage">Image</a></li>
-->
		</ul>

		<div id= "divSelect" class='ui-tabs-panel matrix-tool-top-panel'>
		</div>

		<div id="tab-mmtext" class='matrix-tool-middle-panel'>
			<div id="divText">
                <input type='button' value='Place Text' onClick='PlaceText();' class='buttons'>
                <input type='button' value='Clear' onClick='ClearMatrix();' class='buttons'>
                <input type='button' value='Sync Back' onClick='GetBlockData();' class='buttons'>

				<table border=0><tr><td valign='top'>
					<table border=0>
                    <tr><td>Model:
                        </td><td>
			                <select id='blockList' onChange='selectBlock(this.value, true);'></select>
                        </td></tr>
                    <tr><td>State:
                        </td><td>
                            <select id='blockOnOffSwitch' onChange='blockState()'>
                                <option value='0'>Disabled</option>
                                <option value='1'>Enabled</option>
                                <option value='2'>Transparent</option>
                                <option value='3'>Transparent RGB</option>
                            </select>
                            &nbsp;
                            Auto-Enable:&nbsp;
					        <? PrintSettingCheckbox("Auto-Enable", "autoEnable", 0, 0, "1", "0", "fpp-matrixtools"); ?>
                        </td></tr>

					<tr><td>Text:</td><td colspan=4><textarea cols='64' rows='2' id='inputText'></textarea></td></tr>
					<tr><td>Font :</td>
						<td><select id='fontList' onChange='FontChanged();'>
							</select></td>
						<td width='30px'></td>
						</tr>
					<tr><td>Font&nbsp;Size:</td>
						<td>
<?
$fontSizes = array(
'5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10',
'12' => '12', '14' => '14', '16' => '16', '18' => '18', '20' => '20',
'22' => '22', '24' => '24', '26' => '26', '28' => '28', '30' => '30',
'32' => '32', '34' => '34', '36' => '36', '38' => '38', '40' => '40',
'42' => '42', '44' => '44', '46' => '46', '48' => '48', '50' => '50',
'52' => '52', '54' => '54', '56' => '56', '58' => '58', '60' => '60',
'64' => '64', '70' => '70', '74' => '74', '80' => '80',
);
PrintSettingSelect('Font Size', 'fontSize', 0, 0, '10', $fontSizes, 'fpp-matrixtools');
?>
							&nbsp;
                            Anti-Aliased:&nbsp;
					        <? PrintSettingCheckbox("Anti-Alias", "antiAliased", 0, 0, "1", "0", "fpp-matrixtools"); ?>
							</td>
                        </tr>
					<tr><td>Position:</td>
						<td>
<?
$textPositions = array(
'Center' => 'Center',
'Right to Left' => 'R2L',
'Left to Right' => 'L2R',
'Bottom to Top' => 'B2T',
'Top to Bottom' => 'T2B',
);
PrintSettingSelect('Position', 'textPosition', 0, 0, 'Center', $textPositions, 'fpp-matrixtools');
?>
							</td>
                        </tr>
                    <tr><td>Scroll Speed:</td>
						<td>
<?
$scrollSpeeds = array(
'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
'6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10',
'11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15',
'16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20',
'25' => '25', '30' => '30', '35' => '35', '40' => '40', '45' => '45',
'50' => '50', '55' => '55', '60' => '60', '65' => '65', '70' => '70',
'75' => '75', '80' => '80', '85' => '85', '90' => '90', '95' => '95',
'100' => '100', '120' => '120', '140' => '140', '160' => '160', '180' => '180',
'200' => '200'
);
PrintSettingSelect('Scroll Speed', 'scrollSpeed', 0, 0, '10', $scrollSpeeds, 'fpp-matrixtools');
?>
							(pixels per second)
							</td>
						</tr>
					</table>

				</td><td width='30'>&nbsp;</td><td valign='top'>
				<div id="colpicker2"></div>
				</td></tr></table>
			</div>
		</div>

		<div id="tab-mmdraw" class='matrix-tool-middle-panel'>
			<div id= "divDraw">
					<table border=0><tr><td valign='top'>
						<table border=0>
						<tr><td>Block Fill:</td>
							<td><input type='button' value='Fill' onClick='FillMatrix();' class='buttons'></td>
							</tr>
						<tr><td>Auto-Fill: <? PrintSettingCheckbox("Auto Fill Block", "AutoFill", 0, 0, "1", "0", "fpp-matrixtools", "autoFillChanged"); ?></td>
						</table>
					</table>
			</div>
		</div>

<!--
		<div id="tab-mmimage" class='matrix-tool-middle-panel'>
			<div id= "divImage">
				<fieldset class="fs">
					<legend> Image Tools </legend>
					<select class='blockList'></select> : Off <input type='button' value='On'><br>
					Select Image from Upload Dir<br>
					Scale or crop?<br>
					Crop/Position widget<br>
					<input type='button' value='Make It So'>
				</fieldset>
			</div>
		</div>
-->

		<div id= "divCanvas" class='ui-tabs-panel matrix-tool-bottom-panel'>
			<table border=0>
            <tr><td valign='top'>
			<div>
				<table border=0>
					<tr><td valign='top'>Pallette:</td>
						<td><div class='colorButton red' onClick='setColor("#ff0000");'></div>
							<div class='colorButton green' onClick='setColor("#00ff00");'></div>
							<div class='colorButton blue' onClick='setColor("#0000ff");'></div>
						    <div class='colorButton white' onClick='setColor("#ffffff");'></div>
							<div class='colorButton black' onClick='setColor("#000000");'></div>
						</td>
					</tr>
                    <tr><td>Current Color:</td><td><span id='currentColor'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td></tr>
            <tr><td colspan='2'>Show Color Picker: <? PrintSettingCheckbox("Show Color Picker", "ShowColorPicker", 0, 0, "1", "0", "fpp-matrixtools", "ShowColorPicker"); ?></td></tr>
            <tr><td valign='top' colspan='2'>
                <div id="colpicker"></div>
			</td></tr>
				</table>
			</div>
			</td></tr>
            </table>
			<br>
			<table border=0>
				<tr><td>Matrix</td>
					<td width='40px'>&nbsp;</td>
					<td>Large Pen: <? PrintSettingCheckbox("Large Pen", "LargePen", 0, 0, "1", "0", "fpp-matrixtools", ""); ?></td>
					<td width='40px'>&nbsp;</td>
					<td>Round Pixels: <? PrintSettingCheckbox("Show Round Pixels", "ShowRoundPixels", 0, 0, "1", "0", "fpp-matrixtools", "refreshMatrix"); ?></td>
					<td width='40px'>&nbsp;</td>
					<td>Show Text: <? PrintSettingCheckbox("Show Text Effect", "ShowTextEffect", 0, 0, "1", "0", "fpp-matrixtools"); ?></td>
					<td class='showGridWrapper' width='40px'>&nbsp;</td>
					<td class='showGridWrapper'>Show Grid: <? PrintSettingCheckbox("Show Grid", "ShowGrid", 0, 0, "1", "0", "fpp-matrixtools", "refreshMatrix"); ?></td>
				</tr>
			</table>
				<table>
					<tr><td>
						<canvas id='mmcanvas' class='matrix' width='<? echo $canvasWidth + 1; ?>' height=<? echo $canvasHeight + 1; ?>'></canvas>
                        </td></tr>
                    <tr><td align='center'>
						<span id='warning' style='display: none; color: #ff0000; font-weight: bold;'></span>
                        </td></tr>
                    <tr><td align='center'>
						<div id='log'></div>
					</td></tr>
				</table>
		</div>

	</div>
</div>


<script>

	$("#matrixTabs").tabs({active: 0, cache: true, spinner: "", fx: { opacity: 'toggle', height: 'toggle' } });

	$('#colpicker').colpick({
		flat: true,
		layout: 'rgbhex',
		color: '#ff0000',
		submit: false,
		onChange: function(hsb,hex,rgb,el,bySetColor) {
			if (!bySetColor)
				setColor('#'+hex);
		}
	});

    if (pluginSettings.hasOwnProperty('color') && pluginSettings['color'] != '') {
        currentColor = pluginSettings['color'];
        $('#currentColor').css('background-color', currentColor);
    }

    ShowColorPicker();
	GetBlockList();
    GetFontList();

</script>

