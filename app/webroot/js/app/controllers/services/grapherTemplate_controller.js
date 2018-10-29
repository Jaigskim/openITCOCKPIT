// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.

App.Controllers.ServicesGrapherTemplateController = Frontend.AppController.extend({


    components: ['Ajaxloader'],

    _initialize: function(){
        this.Ajaxloader.setup();
        var self = this;

        /*
         * Bind click event for resetZoom
         */

        $('.resetZoom').click(function(){
            var $this = $(this);
            self.resetZoom($this.attr('start'), $this.attr('end'), $this.attr('ds'), $this.attr('service_id'), $this);
            $this.parent().hide();
        });

        /*
         * Bind load event on image to hide aja loader
         */
        $('.zoomSelection').load(function(){
            var $img = $(this);
            $img.parent().parent().find('.grapherLoader').hide();
        });

        /*
         * Bind right click for zoom out on graph
         */
        document.oncontextmenu = function(){
            return false;
        };
        $('.zoomSelection').mousedown(function(e){
            if(e.button == 2){
                var $img = $(this);
                var start = parseInt($img.attr('start'));
                var end = parseInt($img.attr('end'));

                var newGraphStart = start - (end - start) * 0.5;
                var newGraphEnd = end + (end - start) * 0.5;

                if(newGraphStart < 0){
                    newGraphStart = 0;
                }

                if(newGraphEnd < 0){
                    newGraphEnd = 0;
                }

                $img.parent().parent().parent().parent().parent().find('.graphTime').html(date('d.m.Y H:i', newGraphStart) + ' - ' + date('d.m.Y H:i', newGraphEnd));
                $img.parent().parent().parent().parent().parent().find('.widget-toolbar').show();

                $img.attr('start', newGraphStart);
                $img.attr('end', newGraphEnd);

                $img.parent().parent().find('.grapherLoader').show();
                $img.attr('src', '/services/grapherZoomTemplate/' + encodeURIComponent($img.attr('service_id')) + '/' + encodeURIComponent($img.attr('ds')) + '/' + parseInt(newGraphStart) + '/' + parseInt(newGraphEnd) + '/' + $img.attr('commandUuid'));

                return false;
            }
            return true;
        });

        /*
         * Create imgAreaSelect ongraph images
         */
        //Avoid that we create the imgAreSelect befor the borwser finished image loading
        $('.zoomSelection').load(function(){
            $('.zoomSelection').each(function(key, object){
                /* Every datasource (ds) can hase his own height, depends on the template settings, legend and stuff.
                 * So we need to do a hack to fix this
                 */
                var $object = $(object);
                $object.imgAreaSelect({
                    handles: true,
                    minHeight: $object.innerHeight(),
                    movable: false,
                    resizable: false,
                    autoHide: true,
                    onSelectEnd: self.startZoom
                });
            }.bind(self));
        }.bind(self));
    },

    startZoom: function(img, selection){
        var $img = $(img);
        //links muss man  67px abziehen wegen dem rand links einheit + skala
        //rechts muss man 27px abziehen

        var minX = Math.min(selection.x1, selection.x2);
        var maxX = Math.max(selection.x1, selection.x2);

        if(minX < 0){
            minX = 0;
        }

        if(maxX > ($img.innerWidth() - 67 - 27)){
            maxX = ($img.innerWidth() - 67 - 27);
        }

        var start = parseInt($img.attr('start'));
        var end = parseInt($img.attr('end'));
        var onePixel = (end - start) / ($img.innerWidth() - 67 - 27); //Represents n if seconds for 1 pixel ion graph

        var newGraphStart = Math.round(start + ((minX - 67) * onePixel));
        var newGraphEnd = Math.round(end - (($img.innerWidth() - 67 - 27) - (maxX - 67)) * onePixel);

        //Refresh time of graph for humans
        $img.parent().parent().parent().parent().parent().find('.graphTime').html(date('d.m.Y H:i', newGraphStart) + ' - ' + date('d.m.Y H:i', newGraphEnd));
        $img.parent().parent().parent().parent().parent().find('.widget-toolbar').show();

        //Load new image
        $img.attr('start', newGraphStart);
        $img.attr('end', newGraphEnd);

        $img.parent().parent().find('.grapherLoader').show();
        $img.attr('src', '/services/grapherZoomTemplate/' + encodeURIComponent($img.attr('service_id')) + '/' + encodeURIComponent($img.attr('ds')) + '/' + newGraphStart + '/' + newGraphEnd + '/' + $img.attr('commandUuid'));


        //console.log(date('d.m.Y H:i', newGraphStart));
        //console.log(date('d.m.Y H:i', newGraphEnd));

    },

    resetZoom: function(start, end, ds, service_id, objectThis){
        objectThis.parent().parent().find('.graphTime').html(date('d.m.Y H:i', start) + ' - ' + date('d.m.Y H:i', end));
        objectThis.parent().parent().parent().find('.grapherLoader').show();

        var $img = objectThis.parent().parent().parent().find('.zoomSelection');
        $img.attr('start', start);
        $img.attr('end', end);
        $img.attr('src', '/services/grapherZoomTemplate/' + encodeURIComponent(service_id) + '/' + encodeURIComponent(ds) + '/' + start + '/' + end + '/' + objectThis.attr('commandUuid'));
    }
});