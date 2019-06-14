var DND = function(){
    var background_container,
        pictures,
        grid=[], hCells, vCells, cellSize;
        
    // Calculate horizontal & vertical Cells
    function calculateGrid(){
        var image = $(background_container).children('img');
        cellSize = $(background_container).attr('dnd_cellsize');
        hCells = Math.round($(image).width() / cellSize);
        vCells = Math.round($(image).height() / cellSize);
    }
    
    function flushGrid(){
        grid = [];
        var x, y;
        for(x=0;x<hCells;x++){
            for(y=0;y<vCells;y++){
                var cellIndex = x + (y * hCells);
                grid[cellIndex] = null;
            }
        }
    }
    
    function showMe() {
            var ret = [hCells,vCells,cellSize];
            return ret;
    }
    return {
        init: function(){            
            background_container = $('#background_container');
            pictures = $('#pictures_container').find('img');
            
            // Assing Events on Pictures
            $(pictures).on('mousedown',function(e){
                $(this).css({position:'fixed'});
                $(this).addClass('moveable');              
                $(this).removeClass('moved');
            }).on('mouseup',function(e){
                $(this).removeClass('moveable');
                $(this).addClass('moved');
            }).on('dragstart',function(e){
                e.preventDefault();
            });
            
            // Calc Cells
            calculateGrid();
        },
        initTouch: function(){
            var pic_container = document.getElementById('pictures_container');
            var bg_container = document.getElementById('background_container');
            
            pic_container.addEventListener("touchstart", this.touchHandler, true);
            pic_container.addEventListener("touchmove", this.touchHandler, true);
            pic_container.addEventListener("touchend", this.touchHandler, true);
            pic_container.addEventListener("touchcancel", this.touchHandler, true);
            
            bg_container.addEventListener("touchstart", this.touchHandler, true);
            bg_container.addEventListener("touchmove", this.touchHandler, true);
            bg_container.addEventListener("touchend", this.touchHandler, true);
            bg_container.addEventListener("touchcancel", this.touchHandler, true);
        },
        touchHandler: function (e) {
            var touches = e.changedTouches,
                first = touches[0],
                type = "";
            switch(event.type) {
                case "touchstart": type="mousedown"; break;
                case "touchmove" : type="mousemove"; break;
                case "touchend"  : type="mouseup"; break;
                default: return;
            }

            var simulatedEvent = document.createEvent("MouseEvent");
            simulatedEvent.initMouseEvent(
                type, true, true, window, 1, 
                first.screenX, first.screenY, first.clientX, first.clientY, 
                false, false, false, false, 0, null
            );
                
            $(e.target).trigger(simulatedEvent);
            e.preventDefault();
        },
        getGrid: function(){
            flushGrid();
            
            $(pictures).each(function(imgIndex){
                var cellPosition, imageCenterTop, imageCenterLeft;
                console.log('Image:'+this.src);
                //console.log('Image Offset:'); console.debug($(this).offset());
                //console.log('Image Width:'); console.debug($(this).width());
                
                imageCenterLeft = $(this).offset().left + ($(this).width()/2);
                imageCenterTop  = $(this).offset().top + ($(this).height()/2);
                
                cellPosition ={
                    left: imageCenterLeft - background_container.children('img').offset().left,
                    top : imageCenterTop - background_container.children('img').offset().top
                }
                console.log('Cell Position 1:'); console.debug(cellPosition);
                // Bounds
                if(cellPosition.left < 0){
                    cellPosition.left = 0;
                } else if (cellPosition.left > background_container.children('img').width()){
                    cellPosition.top = background_container.children('img').height();
                }
                
                if(cellPosition.top < 0){
                    cellPosition.top = 0;
                } else if(cellPosition.top > background_container.children('img').height()){
                    cellPosition.left = background_container.children('img').width();
                }
                console.log('Cell Position 2:'); console.debug(cellPosition);
                
                cellPosition.left = Math.round(cellPosition.left / cellSize);
                cellPosition.top  = Math.round(cellPosition.top / cellSize);
                
                var cellIndex = cellPosition.left + (cellPosition.top * hCells);
                
                console.log('Cell Position 3:'); console.debug(cellPosition);
                console.log('Cell Index:'+cellIndex);
                var imgSrc = this.src,
                    subIndex = imgSrc.lastIndexOf('/') + 1,
                    imgName = imgSrc.substr(subIndex);
                
                grid[cellIndex] = imgName;
            })
            
            return grid;
        },
        getLastIndex: function() {
            return hCells*vCells;
        },
        debug: function() {
            return showMe();
        }
    }
}();


$(document).ready(function(){
    var dnd_container = $('#DND');
    dnd_container.on('mousemove',function(e){
        var moveable_element, mouseY, mouseX;
        
        moveable_element = $(this).find('.moveable');
        
        var moveWidth = (moveable_element.width() / 2),
            moveHeight = (moveable_element.height() / 2);
        
        mouseX = (e.clientX || e.pageX) - (moveWidth+10);
        mouseY = (e.clientY || e.pageY) - (moveHeight+10);
        
        moveable_element.css({
            left:mouseX +'px',
            top:mouseY +'px'
        });
        
    }).on('dragstart',function(e){
        e.preventDefault();
    });
});

$(window).load(function(){    
    // Resize Background
    var bgContainer = $('#background_container');
    var bgImage = bgContainer.children('img');
    var scaleUp = false;
    var newWidth, 
        newHeight,
        newCellSize,
        ratioY, 
        ratioX, 
        ratio;
    
    // Get Width Ratio
    if(bgContainer.width() > bgImage.width()){
        ratioX = bgContainer.width() / bgImage.width();
    } else {
        ratioX = bgImage.width() / bgContainer.width();
    }
    // Get Height Ratio
    if(bgContainer.height() > bgImage.height()) {
        ratioY = bgContainer.height() / bgImage.height();
    } else {
        ratioY = bgImage.height() / bgContainer.height();
    }
    // Get The Smallest Ratio
    ratio = (ratioX > ratioY ? ratioX : ratioY);
    
    // Resize Image
    if(bgImage.width() > bgContainer.width()){
        // Scale Down Image
        newWidth = Math.round(bgImage.width() / ratio);
        newHeight = Math.round(bgImage.height() / ratio);
        newCellSize = Math.round(bgContainer.attr('dnd_cellsize') / ratio);
        scaleUp=false;
    } else {
        // Scale Up Image
        newWidth = Math.round(bgImage.width() / ratio);
        newHeight = Math.round(bgImage.height() / ratio);
        newCellSize = Math.round(bgContainer.attr('dnd_cellsize') / ratio);
        scaleUp=true;
    }
    
    // Apply CSS to Background Image
    bgImage.css({
        width: newWidth+'px',
        height: newHeight+'px'
    });
    // Apply new cell size to container
    bgContainer.attr('dnd_cellsize',newCellSize);
    
    // Resize Pictures
    var pictures = $('#pictures_container img')
    if(scaleUp){
        $(pictures).each(function(){
            newWidth = Math.round($(this).width() / ratio);
            newHeight = Math.round($(this).height() / ratio);
            $(this).css({
               width: newWidth+'px',
               height: newHeight+'px'
            });
        });
    } else {
        $(pictures).each(function(){
           newWidth = Math.round($(this).width() / ratio);
           newHeight = Math.round($(this).height() / ratio);
           $(this).css({
               width: newWidth+'px',
               height: newHeight+'px'
           });
        });
    }
    
    // Start DND
    DND.init();
    DND.initTouch();
});