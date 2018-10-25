[//]: # (Links)
[Maps]: /map_module/maps "Maps"
[adding]: /map_module/maps/add (add a new map)
[configure]: #configure "Configure your maps"
[map use]: #map-use "Customize your map"
[blog post]: https://openitcockpit.io/2018/08/16/preview-new-map-module-for-openitcockpit-3-5/

[//]: # (Pictures)
[options collapsed]: /img/docs/maps/maps/buttonopt.png
[map options]: /img/docs/maps/maps/map-menu.png
[enable grid]: /img/docs/maps/maps/enable_grid.png

[//]: # (Content)

## What are maps in openITCOCKPIT?

Maps could be used create custom visualization for technical and non-technical users. Host and Sevices can be added to an map by an intuitive drag and drop editor.

It is possible to upload own background images, and icons that represent a particular state. By default, openITCOCKPIT comes with a bunch of different icons sets.

## How can I create a new map?

Click on
<a class="btn btn-xs btn-success"><i class="fa fa-plus"></i> New</a>
in the upper right corner.

On the [page][adding] that appears you can configure your map.
To learn more about the configuration click [here][configure].

Click on <a class="btn btn-xs btn-primary">Save</a> to create your new map.

Click on <a class="btn btn-xs btn-default">Cancel</a> if you want to discard your changes.

## How can I edit a map?

To edit a map either click on
<a class="btn btn-default btn-xs">&nbsp;<i class="fa fa-cog"></i>&nbsp;</a>
or on
<a class="btn btn-default btn-xs"><span class="caret"></span></a>
and then on
<a class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Edit in Map editor</a>
to go to the edit view.

Ifo you want to edit the map configuration click on
<a class="btn btn-default btn-xs"><span class="caret"></span></a>
and then on
<a class="btn btn-default btn-xs"><i class="fa fa-edit"></i> Edit</a>

To learn more about the configuration click [here][configure].

Click on <a class="btn btn-xs btn-primary">Save</a> to save your map configuration.
Changes in the map editor will be saved automatically.

Click on <a class="btn btn-xs btn-default">Cancel</a> if you want to discard your changes.

## How can I delete a map?

To delete a map either click on
<a class="btn btn-default btn-xs"><span class="caret"></span></a>
and then on
<a class="btn btn-default btn-xs txt-color-red"><i class="fa fa-trash-o"></i> Delete</a>
or go to the edit view and click in the upper right corner on
<a class="btn btn-danger btn-xs"><i class="fa fa-trash-o"></i> Delete</a>.

At deletion a window will pop up asking if you really want to delete it,
confirm to delete.

## How can I view my map?

You click on
<a class="btn btn-default btn-xs"><span class="caret"></span></a>
and then on
<a class="btn btn-default btn-xs"><i class="fa fa-eye"></i> View</a>
or
<a class="btn btn-default btn-xs"><i class="glyphicon glyphicon-resize-full"></i> View in full screen</a>
to view your map in full screen.

## How is a map configured? <span id="configure"></span>

**Container** - Choose a container in which the map will be located. (required)

**Map name** - Choose a reasonable name. (required)

**Map title** - Choose a reasonable title. (required)

**Tenant** - Grand access from the chosen tenants.

## How to use the map editor? <span id="map-use"></span>

Click on
<a class="btn btn-default btn-xs">&nbsp;<i class="fa fa-cog"></i>&nbsp;</a>
or on
<a class="btn btn-default btn-xs"><span class="caret"></span></a>
and then on
<a class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Edit in Map editor</a>
to open the editor.

#### How to set a background?

You can choose a background at the floating menu on the left clicking the **Backgrounds** button <a class="btn btn-default btn-xs"><i class="fa fa-picture-o"></i></a>.
A pop up dialog opens in which you can choose an existing background or upload your own image.

#### Is there an auto hide option for the floating menu?

No, currently not. But we improved the menu and made it smaller.

#### Can I enable a grid or a ruler to outline objects?

Yes, there is and it is easy to enable or disable, just click on ![enable grid] at the top menu.
The option is ticked by default. If you want to disable the grid untick this option.

Right beside this option you can choose a custom grid size.

#### What can I add to the map?

You can add the following:
* Items - Reflect the host or service state.
* Gadgets - Reflect the host or service state.
* Misc. - Lines, icons or static text

You can add them by choosing the appropriate menu point in the left menu an clicking anywhere on the map.

A pop up appears where you can configure what you want to add.

Except of text under misc you have the following options:
First choose what the element represents a host, service, host group, service group or map.

Beneath the chosen you see the current position of the element on your map, which you can change.

Items also have the extra option **iconset** so you can change the display item icon.

#### How can I edit an element?

Double click the element in the editor to open the pop up configuration.
Edit it the way you like, then click on
<a class="btn btn-xs btn-primary">Save</a> to save the element.
Click on <a class="btn btn-xs btn-default">Close</a> if you want to discard your changes.

#### How can I delete an element?

Double click the element in the editor to open the pop up configuration.
Click on <a class="btn btn-xs btn-danger">Delete</a> to delete the element.

#### What are layers?

By editing an element you can also choose a layer. The default layer is 0, so that all elements are on the same level.
To lay elements above others, create a layer with a higher number and choose them in the item edit pop up.

To hide layers just click on the eye icon <i class="fa fa-eye"></i> on the right corner at the bottom of the editing page.
<br><br><br>
To get more information read our [blog post]