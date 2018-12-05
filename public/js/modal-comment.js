$(document).ready(function(){
    // $('#modal-content').modal('show');

    $('.modalArticle').click(function (){
        event.preventDefault();
        console.log("j'ai bien cliqué")
        let articleId = $(this).data('id');
        let parameters = {'articleId': articleId};

        $.post(
            '/admin/article/modalArticle/',
            parameters,
            function (retour){
                var $modal =$('#modal-content');
                $modal.find('.modal-body p').html(retour.content);
                $modal.modal('show');
            },
            'json');
    });


});