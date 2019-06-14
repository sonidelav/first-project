var FTC = function(){
    var drawBoard, drawContext ,bgImage, positions=[], aspectRatio,
        drawData, startFillColor, selectedColor = {r:0,b:0,g:0},
        pixelStack = [];
        
    
    // Private Methods
    function addPositions(pos) {
        for(var i=0;i<pos.length;i++){
            positions[i] = pos[i];
        }
    }
    
    function resizePositions(ratio, scaleUp) {
        for(var i=0; i<positions.length; i++){
            var pixelpos = positions[i].split(",");
            
            if(scaleUp==true){
                pixelpos[0] = Math.round(pixelpos[0] * ratio);
                pixelpos[1] = Math.round(pixelpos[1] * ratio);
            } else {
                pixelpos[0] = Math.round(pixelpos[0] / ratio);
                pixelpos[1] = Math.round(pixelpos[1] / ratio);
            }
            
            positions[i] = pixelpos[0]+','+pixelpos[1];
        }
    }
    
    function initDrawBoard() {
        drawBoard = document.getElementById('drawBoard');
        drawContext = drawBoard.getContext('2d');
    }
    
    function resizeBoard() {
        drawBoard.width = aspectRatio.width;
        drawBoard.height = aspectRatio.height;
    }
    
    function drawBackground() {
            drawContext.drawImage(bgImage,0,0,drawBoard.width,drawBoard.height);
            drawData = drawContext.getImageData(0,0,drawBoard.width, drawBoard.height);
    }
    
    function getAspectRatio() {
        var parentWidth, myWidth, 
            parentHeight, myHeight, 
            newWidth, newHeight, 
            ratioX, ratioY, ratio, scaleUp;
        
        parentWidth = $(drawBoard).parent().width();
        parentHeight = $(drawBoard).parent().height();
        myWidth = bgImage.width;
        myHeight = bgImage.height;
        
        // Get Ratio
        ratioX = parentWidth / myWidth;
        ratioY = parentHeight / myHeight;
        ratio = (ratioX < ratioY ? ratioX : ratioY);
        
        // Resize
        if(myWidth > parentWidth) {
            // Scale Down
            newWidth = myWidth / ratio;
            newHeight = myHeight / ratio;
            scaleUp=false;
        } else {
            // Scale Up
            newWidth = myWidth * ratio;
            newHeight = myHeight * ratio;
            scaleUp=true;
        }
        
        // Resize Positions
        resizePositions(ratio,scaleUp);
        
        aspectRatio = {
            width: newWidth,
            height: newHeight
        }
    }
    
    function getBackground(bgURL) {
        bgImage = new Image;
        bgImage.src = bgURL;
        bgImage.onload = function() {
            getAspectRatio();
            resizeBoard();
            drawBackground();
        };
    }
    
    function getPixelData(x,y) {        
        var imgData = drawContext.getImageData(x,y,1,1);
        return {
            b: imgData.data[2],
            g: imgData.data[1],
            r: imgData.data[0]
        }
    }
    
    function matchStartColor(pixelPos) {
        var r  = drawData.data[pixelPos],
            g  = drawData.data[pixelPos+1],
            b  = drawData.data[pixelPos+2];
        
        return (isInToleranceRange(r,g,b,4));
    }
    
    function isInToleranceRange(r,g,b,tolerance) {
        var red = false, 
            green = false, 
            blue = false, 
            min, max, rgb_value, property;
            
        for(property in startFillColor) {
            
            rgb_value = startFillColor[property];
            
            //console.log('Current Property: '+property+' Value: '+rgb_value);
            
            if(rgb_value < 255 && rgb_value > 0) {
                min = rgb_value - tolerance/2;
                max = rgb_value + tolerance/2;
            } else if(rgb_value == 255){
                min = rgb_value - tolerance;
                max = rgb_value;
            } else if(rgb_value == 0){
                min = rgb_value;
                max = rgb_value + tolerance;
            }
            
            switch(property){
                case 'r':
                    //console.log('Red Channel Tolerance Range: min='+min+' max='+max);
                    red = (r <= max && r >= min);
                    //console.log('Red is '+red);
                    break;
                case 'g':
                    //console.log('Green Channel Tolerance Range: min='+min+' max='+max);
                    green = (g <= max && g >= min);
                    //console.log('Green is '+green);
                    break;
                case 'b':
                    //console.log('Blue Channel Tolerance Range: min='+min+' max='+max);
                    blue = (b <= max && b >= min);
                    //console.log('Blue is '+blue);
                    break;
            }
        }
        
        return (red && green && blue);
    }
    
    function colorPixel(pixelPos) {
        drawData.data[pixelPos] = selectedColor.r;
        drawData.data[pixelPos+1] = selectedColor.g;
        drawData.data[pixelPos+2] = selectedColor.b;
        drawData.data[pixelPos+3] = 255;
    }
    
    // Public Object
    return {
        init: function(posList, bgURL) {
            addPositions(posList);
            initDrawBoard();
            getBackground(bgURL);
        },
        getPixels: function(){
            var pixels = [];
            
            for(var i=0;i<positions.length;i++){
                var pos = positions[i].split(",");
                pixels[i] = getPixelData(pos[0],pos[1]);
            }
            
            return pixels;
        },
        fillColor: function(x,y){
            //var pixelStack = [[x,y]];
            var boardWidth = drawBoard.width;
            var boardHeight = drawBoard.height;
            //drawData = drawContext.getImageData(0,0,boardWidth, boardHeight);
            
            startFillColor = getPixelData(x,y);
            
            // If We Try to Colorize with the same color ignore it is useless!
            if(
                startFillColor.r == selectedColor.r && 
                startFillColor.g == selectedColor.g && 
                startFillColor.b == selectedColor.b
            ){
                return;
            }
            
            pixelStack.push([x,y]);
            
            while(pixelStack.length) {
                var newPos, _x, _y, pixelPos, reachLeft, reachRight;
                
                newPos = pixelStack.pop();
                _x = newPos[0];
                _y = newPos[1];
                
                pixelPos = (_y*boardWidth + _x) * 4;
                
                while(_y-- >= 0 && matchStartColor(pixelPos)) {
                    pixelPos -= boardWidth * 4;
                }
                
                pixelPos += boardWidth * 4;
                ++_y;
                reachLeft = false;
                reachRight = false;
                
                while(_y++ < boardHeight-1 && matchStartColor(pixelPos)) {
                    colorPixel(pixelPos);
                    
                    if(_x > 0) {
                        if(matchStartColor(pixelPos - 4)) {
                            if(!reachLeft) {
                                pixelStack.push([_x-1, _y]);
                                reachLeft = true;
                            }
                        } else if(reachLeft) {
                            reachLeft = false;
                        }
                    }
                    
                    if(_x < boardWidth-1) {
                        if(matchStartColor(pixelPos + 4)) {
                            if(!reachRight) {
                                pixelStack.push([_x + 1, _y]);
                                reachRight = true;
                            }
                        } else if(reachRight) {
                            reachRight = false;
                        }
                    }
                    
                    pixelPos += boardWidth * 4;
                }
            }
            drawContext.putImageData(drawData, 0, 0);
        },
        selectColor: function(red, green, blue){
            selectedColor.r = red;
            selectedColor.g = green;
            selectedColor.b = blue;
        },
        reset: function() {
            resizeBoard();
            drawBackground();
        },
        resize: function() {
            getAspectRatio();
            resizeBoard();
            drawBackground();
        }
    }
}();

$(window).load(function(){
    // Initialize Events
    $('.color_box').on('click',function(e){
        var color_from_attr = $(this).attr('style');
        var color_hex = color_from_attr.substr(color_from_attr.lastIndexOf('#')+1,6);
        
        var red = color_hex.substr(0,2);
        var green = color_hex.substr(2,2);
        var blue = color_hex.substr(4,2);
        
        red = parseInt(red,16);
        green = parseInt(green,16);
        blue = parseInt(blue,16);
        
        FTC.selectColor(red,green,blue);
        
        e.preventDefault();
        e.stopPropagation();
        return;
    });
    
    $('#drawBoard').on('click',function(e){
        var offsetTop, offsetLeft, mouseX, mouseY;
        
        offsetLeft = $(this).offset().left;
        offsetTop = $(this).offset().top;
        
        mouseX = (e.clientX || e.pageX) - offsetLeft;
        mouseY = (e.clientY || e.pageY) - offsetTop;

        FTC.fillColor(mouseX, mouseY);
        
        e.preventDefault();
        e.stopPropagation();
        return;
    });
});

$(window).resize(function(){
    FTC.resize();
});