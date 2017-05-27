/*
 * This file contains misc functions and event handlers
 *
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

/**
* Closure to convert the JSON content to an array of arrays.
*/
function jsonToArray(columns) {
    return function(sSource, aoData, fnCallback) {
        $.ajax( {
            dataType: 'json',
            type: "GET",
            url: sSource,
            data: aoData,
            success: function (data) {
                newdata = {
                    aaData: []
                }
                for (var i=0; i < data.length; ++i) {
                    newdata.aaData[i] = [];
                    for (var j=0; j < columns.length; ++j) {
                        newdata.aaData[i].push(data[i][columns[j]]);
                    }
                }
                fnCallback(newdata)
            }
        });
    }
}

/**
* Initiate rating system
*/
function initRating() {
    $('.rate').each(function(index, element) {
        if($(this).children().length == 0) {
            $($(this)).rate({
                path: _BASE_URL + '/media/images/rate/',
                start: $(this).attr('data-rating'),
                click: function(rating, event) {
                    var self = this;
                    $.ajax({
                        url: _BASE_URL + '/book/put?format=json',
                        type: 'POST',
                        data: {
                            id: this.attr('id'),
                            columnName: 'Rating',
                            value: rating
                        },
                        success: function(data) {
                            if (data == rating) {
                                self.attr('data-rating', data);
                            } else {
                                jError('There was an error updating the rating, please try again.', {
                                    HorizontalPosition: 'center',
                                    VerticalPosition: 'center'
                                });
                            }
                        },
                        error: function() {
                            jError('Server error.', {
                                HorizontalPosition: 'center',
                                VerticalPosition: 'center'
                            });
                        }
                    })
                }
            });
        }
    });
}

/**
* Initiate tooltips on table rows
*/
function initTooltip(bookTable) {
    var nodes = $(bookTable.fnGetNodes());
    nodes.each(function() {
        $(this).qtip({
            content: {
                text: 'Loading...',
                ajax: {
                    url: _BASE_URL + '/book/tooltip/'+this.id,
                    data: 'format=json',
                    dataType: 'json',
                    success: function(data) {
                        var content = 'SYNOPSIS:<br/>';
                        content += data[0].Synopsis;
                        this.set('content.text', content);
                    }
                }
            },
            position: {
                my: 'bottom left',
                at: 'top center',
                target: 'event'
            },
            style: {
                classes:  'ui-tooltip-blue ui-tooltip-shadow ui-tooltip-rounded'
            },
            widget: true,
            width: 500,
            show: {
                delay: 1000,
                effect: function(offset) {
                    $(this).fadeIn(500);
                }

            }
        })
    })
}

/**
 * Initiates the book table
 */
function initBookTable() {
    return $('#booktable').dataTable({
        bJQueryUI: true,
        bProcessing: true,
        sAjaxSource: _BASE_URL + '/book/getbyuser?format=json',
        fnServerData: jsonToArray(['BookId', '', 'Isbn', 'Title', 'Author', 'Publisher', 'DatePublished',
            'DateAdded', 'CategoryName', 'Rating']),
        bStateSave: true,
        bInfo: true,
        bAutoWidth: false,
        sPaginationType: 'full_numbers',
        aaSorting: [[3, 'asc']],
        sDom: '<"H"lCfr>t<"F"ip<',
        oColVis: {
            aiExclude: [0, 1]
        },
        aoColumnDefs: [
        {
            bSearchable: false,
            bSortable: false,
            aTargets: [0, 1]
        },
        {
            bVisible: false,
            aTargets: [0]
        },

        {
            sClass: 'detailsCol read_only',
            aTargets: [ 1 ]
        },
        {
            sClass: 'read_only',
            aTargets: [0, 7, 9]
        },
        {
            sClass: 'datepicker',
            aTargets: [ 6 ]
        },
        {
            sClass: 'isbn',
            aTargets: [2]
        },
        {
            sName: 'DatePublished',
            aTargets: [6]
        },
        {
            fnRender: function(obj) {
                return '<a href="<?php echo __BASE_URL; ?>/book/show/'+ obj.aData[obj.iDataColumn] +'">' + obj.aData[obj.iDataColumn] + ' (view)</a>';
            },
            bUseRendered: false,
            aTargets: [0]
        },
        {
            fnRender: function(obj) {
                return '<div class="rate" id="'+obj.aData[0] +'" data-rating="'+obj.aData[9]+'"></div>';
            },
            bUseRendered: false,
            aTargets: [9]
        },
        {
            fnRender: function(obj) {
                return '<img src="' + _BASE_URL + '/media/images/details_open.png"/>';
            },
            aTargets: [1]
        }
        ],
        fnRowCallback: function(row, data, index, iDisplayIndexFull) {
            return row;
        },
        fnDrawCallback: function(obj) {
            initTooltip(this);
            initRating();
        }
    })

    /**
    * Makes the booktable editable
    */
    .makeEditable({
        sAddURL: _BASE_URL + '/book/post?format=json',
        sUpdateURL: _BASE_URL + '/book/put?format=json',
        sDeleteURL: _BASE_URL + '/book/delete?format=json',
        aoColumns: {
            5: {
                onblur: 'ignore',
                submit: 'ok',
                cancel: 'cancel'
            }
        },
        fnShowError: function(message, action) {
            jError(message, {
                HorizontalPosition: 'center',
                VerticalPosition: 'center'
            });
        }
    });
}

/**
 * Sets up the date pickers
 */
function initDatepickers() {
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('#booktable').delegate('.datepicker input', 'focus', function() {
        $this = $(this);
        $($this).datepicker({
            dateFormat: 'yy-mm-dd',
            disabled: true
        });
    });
}


/**
 * Attaches various standalone event handlers
 */
function initEvents(bookTable) {
    /**
    * Click event to look up ISBN data
    */
    $('#isbnLookup').click(function(event) {
        var isbn = $('#isbn');
        $.ajax({
            url: _BASE_URL + '/book/find/'+isbn.val(),
            dataType: 'json',
            data: 'format=json',
            success: function(data) {
                var book = data[0];
                if (book.Isbn === null) {
                    jError('No data found, please make sure the ISBN is correct.', {
                        HorizontalPosition: 'center',
                        VerticalPosition: 'center'
                    });
                } else {
                    isbn.val(book.Isbn);
                    $('input[name="form[Title]"]').val(book.Title);
                    $('input[name="form[Author]"]').val(book.Author);
                    $('input[name="form[Publisher]"]').val(book.Publisher);
                    $('input[name="form[DatePublished]"]').val(book.DatePublished);
                    $('input[name="form[Synopsis]"]').val(book.Synopsis);
                    $('input[name="form[CategoryName]"]').val(book.CategoryName);
                    $('input[name="form[Tags]"]').val(book.Tags + ',');
                }
            },
            error: function() {
                jError('Server error.', {
                    HorizontalPosition: 'center',
                    VerticalPosition: 'center'
                });
            }
        })
        event.preventDefault();
        event.stopPropagation();
    })

    /**
    * Key event to apply filtering per column
    */
    var fInput = $("thead input");
    fInput.keyup(function() {
        /* Filter on the column (the index) of this element */
        bookTable.fnFilter(this.value, fInput.index(this) + 2 );
        event.stopPropagation();
    });

    /**
    * Click-event to reset filters
    */
    $('#filterReset').click(function() {
        fInput.each(function(index) {
            bookTable.fnFilter('', index, false, false, false);
            this.value = '';
        })
        event.stopPropagation();
    })

    /**
    * Click-event to open/close details row
    */
    $('.detailsCol img').live( 'click', function () {
        var row = this.parentNode.parentNode;
        if (this.src.match('details_close')) {
            // This row is already open - close it
            $(row).toggleClass('detailsOpen');
            this.src = _BASE_URL + '/media/images/details_open.png';
            bookTable.fnClose(row);
        }
        else {
            // Open this row
            this.src = _BASE_URL + '/media/images/details_close.png';
            $(row).toggleClass('detailsOpen');
            newrow = bookTable.fnOpen(row, detailsLayout(row), 'details' );
            fetchDetails(newrow, row.id);
        }
    });

    /**
    * Click-event to close all detail rows
    */
    $('#closeAllDetails').click(function(event) {
        $('.detailsOpen').each(function() {
            $('img:first', this).attr('src', _BASE_URL + '/media/images/details_open.png');
            $(this).toggleClass('detailsOpen');
            bookTable.fnClose(this);
        })
    });

    /**
    * Sets up the 'Processing' div during XHR.
    */
    //    $('.dataTables_processing')
    //    .ajaxStart(function() {
    //        $(this).css('visibility', 'visible');
    //    })
    //    .ajaxStop(function() {
    //        $(this).css('visibility', 'hidden');
    //    })

    $('#help').dialog({
        autoOpen: false,
        width: 'auto'
    });

    $('#btnHelp').click(function(event) {
        $('#help').dialog('open');
    })

    var elem = $('#isbn');
    var okbtn = $('#btnAddNewRowOk');
    var fetchbtn = $('#isbnLookup');
    elem.next().after('<span class="error">');
    elem.keyup({
        ok: okbtn,
        fetch: fetchbtn
    }, function(event) {
        fetchbtn.attr('disabled', 'disabled');
        if (elem.val().length > 0) {
            event.data.ok.attr('disabled', 'disabled');
            $('.error').html('Invalid ISBN');
        }
        // validation goes here
        if (validIsbn(elem.val()) || elem.val().length == 0) {
            $('.error').html('');
            event.data.ok.attr('disabled', '');
            if (elem.val().length != 0) {
                event.data.fetch.attr('disabled', '');
            }
        }
    })
}

function validIsbn(isbn) {
    isbn = String(isbn);
    var valid = true;
    isbn = isbn.replace(/-/g, '');
    isbn = isbn.replace(/ /g, '');
    if (isbn.length != 10 && isbn.length != 13) {
        valid = false;
    }
    if (isNaN(isbn)) {
        valid = false;
    }
    return valid;
}

/**
* Initial html to render when opening details row
*/
function detailsLayout(row) {
    return '<div>Loading contents...</div>';
}

var detailsCache = [];

/**
* Fetches book details from cache or server
*/
function fetchDetails(row, id) {
    // if we have the details cached, use it
    if (detailsCache[id]) {
        setDetails(row, detailsCache[id]);
    } else {
        // perform new ajax request
        $.ajax({
            url: _BASE_URL + '/book/details/'+id,
            dataType: 'json',
            data: 'format=json',
            success: function(data) {
                // cache the data
                detailsCache[id] = data[0];
                setDetails(row, data[0]);
            }
        })
    }
}

/**
 * Callback for editable inputs in the details view
 */
function editableCallback(value, settings) {
    $this = $(this);
    console.log($this);
    var id = $this.parent().parent().parent().prev().attr('id');
    var columnName = $this.attr('data-name');
    $.ajax({
        url: _BASE_URL + '/book/put?format=json',
        type: 'POST',
        data: {
            'id': id,
            'columnName': columnName,
            'value': value
        },
        success: function(data) {
            console.log(id);
            detailsCache[id] = null;
        }
    })
    return value;
}


/**
* Inserts book details into the DOM
*/
function setDetails(row, book) {
    var details = $('.details', row);
    details.text('');
    var syn = $('<div class=details-block>').html('<h3>Synopsis:</h3>').appendTo(details);
    $('<div class="editarea" data-name="Synopsis">').html(book.Synopsis).appendTo(syn);
    var rev = $('<div class=details-block>').html('<h3>Review:</h3>').appendTo(details);
    $('<div class="editarea" data-name="Review">').html(book.Review).appendTo(rev);
    var tags = $('<div class=details-block>').html('<h3>Tags:</h3>').appendTo(details);
    $('<span class="editable" data-name="Tags">').html(book.Tags).appendTo(tags);

    // make the details editable
    $('.editarea', row).editable(editableCallback, {
        type: 'textarea',
        event: 'dblclick',
        cancel: 'Cancel',
        submit: 'Save',
        tooltip: 'Doubleclick to edit...',
        height: '150'
    });
    // make tags editable
    $('.editable', row).editable(editableCallback, {
        event: 'dblclick',
        tooltip: 'Doubleclick to edit...'
    });
}