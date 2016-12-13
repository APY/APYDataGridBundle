$(function() {
  $('.grid-column-colore, .grid-column-simboloColore, .grid-column-coloreSfondo, .grid-column-coloreTesto').each(function(){
    var colore = '#' + $(this).text();
    $(this).html('<i style="background-color: ' + colore + ';" class="showcolor"></i>');
  });

  //$(".gridMassActionSubmit").on("click", function(e) {
  //    var azioneSelezionata = $(this).siblings("select.gridMassActionSelect").find("option:selected").text();
  //    if (!confirm('Procedere con l\'azione selezionata "' + azioneSelezionata + '"?')) {
  //        e.stopImmediatePropagation();
  //        e.preventDefault();
  //    }
  //});

  $('.grid input.action').change(function(){
    var tr = $(this).parents('tr');
    if (tr.hasClass('checked')) {
      tr.removeClass('checked');
    } else {
      tr.addClass('checked');
    }
  });

  $('.grid-mass-selector').change(function (){
    $('.grid input.action').trigger('change');
  });

  function scrollTo(position, duration) {
    var steps = duration / 6; //1 step = 6ms
    var scrollStep = (position - window.scrollY) / steps;
    var iterations = 0;
    var tolerance = 15;

    var interval = setInterval(function() {
      window.scrollTo(0, window.scrollY + scrollStep);
      iterations += 1;
      if (Math.abs(window.scrollY - position) < tolerance || iterations - 5 > steps){
        clearInterval(interval);
      }
    }, 6);
  }

  function highlight($row){
    $row.css('background-color', 'rgb(255, 255, 152)');
    setTimeout(function(){
      $row
        .css('background-color', '')
        .css('transition', 'background-color 2400ms linear');
    }, 10);

    setTimeout(function(){
      $row.css('transition', '');
    }, 2400);
  }

  if ($('.grid').length) {

    var gridFormId = $('.grid > form').attr('id');
    var GRID_ACTION_STORAGE_KEY = 'grid-action-entity-id_';
    var storageKey = GRID_ACTION_STORAGE_KEY + gridFormId;

    $('.grid-row-actions').on('click', 'a', function(evt){
      var $row = $(this).parents('tr[data-entity-id]').first();
      var entityId = $row.data('entityId');
      try{
        sessionStorage.setItem(storageKey, JSON.stringify({entityId: entityId, validUntil: moment().add(60, 'm').format()}));
      }
      catch(e){
        console.err(e);
      }
    });

    var gridRecallData = null;
    try{
      gridRecallData = JSON.parse(sessionStorage.getItem(storageKey));
    }
    catch(e){
      console.log(e);
    }

    if ( gridRecallData && gridRecallData.entityId && moment().isBefore(moment(gridRecallData.validUntil)) ) {

      var $row = $('tr[data-entity-id=' + gridRecallData.entityId + ']').first();
      var gridAutoScroll = window.sessionStorage.getItem('gridAutoScroll');

      if( $row && gridAutoScroll ){
        scrollTo($row.offset().top - 100, 1200);
        highlight($row);
      }

      try{
        sessionStorage.removeItem(storageKey);
      }
      catch(e){
        console.log(e);
      }
    }
  }
});
