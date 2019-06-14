/*
 * jQuery Script
 *
 **/
var StaticDisplayOpen = false;
var FTC = undefined;
var DND = undefined;

$(document).ready(function(){
    
    var Answer = null;
    var ans = '';
    
    // Radio Buttons Handle
    $('label:has(input[type=radio])').click(function(){
        
        $('input[type=radio]').each(function(){
                $(this).parent('label').css('background-color','');
        });
        $(this).children('input').attr('checked', true);
        $(this).css('background-color','#007ACC');
        Answer = $(this).children('input').val();
    });
    
    // Checkbox Buttons Handle
    $('label:has(input[type=checkbox])').click(function(){
       $(this).children('input').attr('checked', !$(this).children('input').attr('checked'));
       $(this).css('background-color','');
        
       $(':checkbox:checked').each(function(){
           $(this).parent('label').css('background-color','#007ACC');
           ans += $(this).val()+"~";
       });
       
       Answer = ans;
       ans='';
    });    
    
    // Input Text Handle
    
    $('#FillGapsContainer input').change(function(){
        
        // Fill Ans With Typed Values
        $('#FillGapsContainer input').each(function(){
            ans += $(this).val() + '\u2502';
        });
        Answer = ans;
        ans = '';
    });
    
    if(
        $('#FillGapsContainer input').val() != '' && 
        $('#FillGapsContainer').length > 0
    ) {
         // Fill Ans With Typed Values
        $('#FillGapsContainer input').each(function(){
            ans += $(this).val() + '\u2502';
        });
        Answer = ans;
        ans = '';
    }
    
    // Send Answers
    $('#subForm').submit(function(){
        // Collect Matching Drag & Drop Values
        var $groups = $('#board .dropGroup');
        // Check if we have static display question type.
        var staticDisplay = $('#MediaContainerMono').length > 0 ? true : false;
        
        if($groups.length > 0)
        {
            // Check if at last one drag box has dropped.
            var _atLastOne = $groups.find('.dragBox').length;
            if(!_atLastOne) return false;
            
            // Matching Groups
            $groups.each(function(){
               ans += $(this).children('p').text(); // Get Group Title
               ans += '\u2562'; // Split Group From Answers
               var $dropBoxes = $(this).children('.dropBox');
               if($dropBoxes.length > 0) {
                   $dropBoxes.each(function(){
                       var dropAnswer = '';
                       if($(this).children().length > 0) {
                            dropAnswer = $(this).children('span').text();
                       }
                       ans += dropAnswer + '\u2524';
                   });
               }
               ans += '\u2562'; // End Of Group Answers Data
            });
            
            Answer = ans;
            ans = '';
        } 
        else 
        {
            // Matching
            var $dropBoxes = $('#board .dropBox');
            if($dropBoxes.length > 0) {
                $dropBoxes.each(function(){
                    var thisValue = $(this).children('span').length > 0 ? $(this).children('span').text() : '';
                    ans += thisValue + '\u2524';
                });

                Answer = ans;
                ans = '';
            }
        }
        
        //console.log('Send Answer',Answer);
        
        if(staticDisplay){
            Set_Cookie('ans', 'static-display');
            return true;
        }
        // Drag n Drop
        if(DND !== undefined){
            Answer = JSON.stringify({
                type: 'DND',
                lastCell: DND.getLastIndex(),
                data: DND.getGrid()
            });
        }
        // Fill The Color
        if(FTC !== undefined){
            Answer = JSON.stringify({
                type: 'FTC',
                data: FTC.getPixels()
            });
        }
        
        if(Answer)
        {
            Set_Cookie('ans', Answer);
            return true;
        }
        
        return false;
    });

    // Fade Ins
    if(!autorefresh) {
        $('#taskbar').fadeIn(500);
    }
    
    // Refresh Screen
    $('#clearBtn').click(function(){
        if($('#qPanel:has(#AnswerContainer)').length || $('#qPanel:has(#AnswerContainerMono)').length) {            
            $('#answersForm').fadeOut(250, function(){
                $('#answersForm label').css('background-color','');

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
        } else if ($('#qPanel:has(#MatchingContainer)').length) {
            var $answersContainer = $('#MatchingContainer').children('#answers');
            var $boardContainer = $('#MatchingContainer').children('#board'); 
            var $dropBoxes = $boardContainer.find('.dropBox');
            
            $dropBoxes.each(function(){
                $(this).children('span').appendTo($answersContainer);
            });
        }
        
        if(FTC !== undefined) {
            FTC.reset();
        }
        
        Answer = null;
        calcInterface();
        return false;
    });
    
    // Finish Exam
    $('#finishBtn').click(function(){
        $('<div class="dim"></div>').appendTo('body').fadeIn(1000);
        clearInterval(virtualTimer);
        $(this).parent('form').submit();
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
    
    // Web Interface    
    // Calculate Images
    //$('#qPanel img').on('load',CalculateImages);
    
    calcInterface();
    
});

// Set Cookie
function Set_Cookie(name, value, expires, path, domain, secure)
{
    document.cookie = name + "=" + escape(value) +
    ((expires) ? ";expires=" + expires.toGTMString() : "") +
    ((path) ? ";path=" + path : "") +
    ((domain) ? ";domain=" + domain : "") +
    ((secure) ? ";secure" : "");
}


/* ACTIONS */

function finishExam()
{
    $('<div class="dim"><form id="finishForm" action="index.php" method="post"><input type="hidden" name="finishExam" value="1" /></form></div>').appendTo('body').fadeIn(1000);
    $('form#finishForm').submit();
    clearInterval(virtualTimer);
}

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

/* UI */

$(window).resize(function(){
   calcInterface();
   $('#qPanel img').each(CalculateImages);
});

$(window).load(function(){
   $('#qPanel img').each(CalculateImages);
});

function calcInterface() {
    $('.player').css({width:'80%'});
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
    _answerBoxes = $('.answer').length;
    _answerBoxHeight = Math.round($('#answersForm').height()/_answerBoxes) - 8;
    
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
        //console.log('Groups',$groups.length);
        
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
            
            //console.log('UI','Group:'+this.id+", Height:"+$(this).height()+", Boxes:"+$dropBoxes.length);
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
    parentHeight = $(this).parent().height();
    parentWidth  = $(this).parent().width();        
    // Image In Answer Box
    if($(this).parent().get(0).tagName.match(/label/i)) {
        ratioX = (parentWidth-10) / $(this).width();
        ratioY = (parentHeight-10) / $(this).height();
        ratio = (ratioX < ratioY) ? ratioX : ratioY;

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
        containerWidth = $(this).parent().width();
        pageWidth = $('html').width();
        leftPos = 0;

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