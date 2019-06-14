var StaticDisplayOpen = false;


$(document).ready(function(){
       
    // Disable Controls
    $('button').click(function(){return false;});
    $('form').submit(function(){return false;});
    
    $('#taskbar').fadeIn(1000);
    
    // Make a div above taskbar
    $('<div class="taskdim"></div>').appendTo('#taskbar');
    
    
    // Multi_OneCorrect & Multi_TwoPlusCorrect
    $('label:has(input[type=radio])').click(function(){
        
        $('input[type=radio]').each(function(){
                $(this).parent('label').css('background-color','');
        });
        $(this).children('input').attr('checked', true);
        $(this).css('background-color','#007ACC');
        
    });
    
    $('label:has(input[type=checkbox])').click(function(){
        
       $(this).children('input').attr('checked', !$(this).children('input').attr('checked'));
       $(this).css('background-color','');
        
       $(':checkbox:checked').each(function(){
           $(this).parent('label').css('background-color','#007ACC');
       });
    });
    
    
    // Input Text Handle
    $('#FillGapsContainer input').change(function(){
        var ans = '';
        
        // Fill Ans With Typed Values
        $('#FillGapsContainer').find('input').each(function(){
            ans += $(this).val() + '\u2502';
        });
        
        Answer = ans;
    });
    

    // Refresh Screen
    $('#clearBtn').click(function(){
        if($('#qPanel:has(#AnswerContainer)').length || $('#qPanel:has(#AnswerContainerMono)').length) {            
            $('#answersForm').fadeOut(250, function(){
                $('#answersForm label').css('background-color','#22225C');

                // CheckBoxes
                $(':checkbox:checked').each(function(){
                   $(this).attr('checked',false); 
                });
            }).fadeIn(250);
        }
        else if ($('#qPanel:has(#FillGapsContainer)').length) {
            $('#FillGapsContainer').fadeOut(250,function(){
                $(this).find('input').each(function(){
                   $(this).val('');
                });
            }).fadeIn(250);
        }
        
        Answer = null;
        return false;
    });
    
    // Open Static Display
    $('#MediaContainerMono img').click(function(){
       if(!StaticDisplayOpen) {
           $('#title').slideUp(1000);
           $('#taskbar').slideUp(1000);
           $(this).css({
               position:'fixed'
           }).css({
               top:'0px',
               left: (parseInt($('html').css('width'))/2) - (parseInt($(this).css('width'))/2)+"px"
           });
           StaticDisplayOpen = true;
       } else {
           $('#title').slideDown(1000);
           $('#taskbar').slideDown(1000);
           $(this).css({
               position:'absolute'
           }).css({
               left: (parseInt($('#MediaContainerMono').css('width'))/2) - (parseInt($(this).css('width'))/2) + "px"
           });
           StaticDisplayOpen = false;
       }
    });
    
    // Initialize Drag n Drop
    $('.dragBox').each(function(){
        this.addEventListener('dragstart', drag, false);
        this.draggable = true; 
    });
    
    $('.dropBox').each(function(){
       this.addEventListener('drop', drop, false);
       this.addEventListener('dragover', allowDrop, false);
    });
    
    // Calculate Web Interface

    //$('#qPanel img').on('load',CalculateImages);
    
    calcInterface();
});


// Drag 'n Drop HTML 5
function allowDrop(ev) {
    ev.preventDefault();
}
function drag(ev) {
    ev.dataTransfer.setData("Text",ev.target.id);
}
function drop(ev) {
    ev.preventDefault();
    data = ev.dataTransfer.getData("Text");
    targetElement = ev.target;
    
    if(targetElement.id.match(/drop/gi) || targetElement.id.match(/answers/gi)) {
        ev.target.appendChild(document.getElementById(data));
    }
    calcInterface();
}

$(window).resize(function(){
   calcInterface();
   $('#qPanel img').each(CalculateImages);
});

$(window).load(function(){
   $('#qPanel img').each(CalculateImages);
});

function calcInterface() {
    // Calculate Containers Size
    var qtitleHeight = $('#qPanel #title').css('height');
    var taskbarHeight = $('#taskbar').css('height');
    qtitleHeight = parseInt(qtitleHeight);
    taskbarHeight = parseInt(taskbarHeight);
    
    qtitleHeight += 10;
    
    $('.container').css({
        top : qtitleHeight+"px"
    });
    $('#qPanel').css({
        bottom : taskbarHeight+"px"
    });
    
    // Calculate Answers Box
    var _answerBoxes = $('.answer').length;
    var _answerBoxHeight = Math.round($('#answersForm').height()/_answerBoxes) - 8;
    
    $('.answer').css({
        height: _answerBoxHeight+"px",
        margin: "8px 0px 0px 0px"
    });

    // Calculate Answers Text    
    var answerTextHeight = $('.answer').find('span').height();
    var answerLabelHeight = $('.answer').find('label').height();
    var textTop = (answerLabelHeight/2) - (answerTextHeight/2);
    $('.answer').find('span').css('top',textTop+"px");
    
    // Calculate Taskbar
    if($('#tooltip p').length < 1) {
        var tooltipText = $('#tooltip').html();
        $('#tooltip').html('<p>'+tooltipText+'</p>');
    }
    
    // Calculate Drop & Drag Boxes
    var matchingContainer = document.getElementById('MatchingContainer');
    if(matchingContainer) {
        var $groups = $(matchingContainer).find('.dropGroup');
        console.log('Groups',$groups.length);
        
        $groups.each(function(){
            var groupHeight = $(this).height();
            var groupWidth  = $(this).width();
            var titleHeight = $(this).children('p').height();
            var $dropBoxes = $(this).children('.dropBox');
            
            $dropBoxes.each(function(){
                var boxWidth = $(this).width();
                
               $(this).height(Math.round((groupHeight/$dropBoxes.length)-titleHeight));
               $(this).css({
                   position:'relative',
                   left : (groupWidth/2)-(boxWidth/2)
               });
            });
            
            console.log('UI','Group:'+this.id+", Height:"+$(this).height()+", Boxes:"+$dropBoxes.length);
        });
        
        $('.dragBox, .text').each(function (){
           var boxHeight = $(this).height();
           var $text = $(this).children('p');
           var topPos = Math.round((boxHeight/2) - ($text.height()/2));
           $text.css({
               top:  topPos+"px",
               position:"relative"
           })
        });
        
    }
}

function CalculateImages() {
    var parentHeight = $(this).parent().height();
    var parentWidth  = $(this).parent().width();        
    // Image In Answer Box
    if($(this).parent().get(0).tagName.match(/label/i)) {
        var ratioX = (parentWidth-10) / $(this).width();
        var ratioY = (parentHeight-10) / $(this).height();
        var ratio = (ratioX < ratioY) ? ratioX : ratioY;

        $(this).css({
            height : ($(this).height() * ratio) + "px",
            width  : ($(this).width() * ratio) + "px"
        }).css({
            top    : ((parentHeight/2) - ($(this).height()/2)) + "px",
            position: 'relative'
        });
    } 
    // Image In a File Container
    else if($(this).parent().hasClass('FileContainer')) {
        ratioX = (parentWidth-20) / $(this).width();
        ratioY = (parentHeight-20) / $(this).height();
        ratio = (ratioX < ratioY) ? ratioX : ratioY;

        $(this).css({
            height : ($(this).height() * ratio) + "px",
            width  : $(this).width() * ratio + "px"
        }).css({
            left   : ((parentWidth/2) - ($(this).width()/2)) + "px",
            top    : ((parentHeight/2) - ($(this).height()/2)) + "px",
            position: 'relative'
        });    
    }
    // Static Display
    else if($(this).parent().attr('id').match(/MediaContainerMono/gi)) {
        var containerWidth = $(this).parent().width();
        var pageWidth = $('html').width();
        var leftPos = 0;

        if(!StaticDisplayOpen) {
            leftPos = (containerWidth/2)-($(this).width()/2);
        } else {
            leftPos = (pageWidth/2)-($(this).width()/2);
        }

        $(this).css({
            left:leftPos + "px",
            position:'relative'
        });
    }
    // Media Container Image
    else if($(this).parent().attr('id').match(/MediaContainer/gi)) {
        ratioX = (parentWidth-40) / $(this).width();
        ratioY = (parentHeight-40) / $(this).height();
        ratio = (ratioX < ratioY) ? ratioX : ratioY;

        $(this).css({
           height: ($(this).height() * ratio) + "px",
           width : ($(this).width()  * ratio) + "px"
        }).css({
            left : ((parentWidth/2) - ($(this).width()/2)) + "px",
            top  : ((parentHeight/2) - ($(this).height()/2)) + "px",
            position:'relative'
        });
    }
}