YUI.add("moodle-mod_bigbluebuttonbn-broker",function(e,t){M.mod_bigbluebuttonbn=M.mod_bigbluebuttonbn||{},M.mod_bigbluebuttonbn.broker={datasource:null,polling:null,bigbluebuttonbn:{},init:function(t){this.datasource=new e.DataSource.Get({source:M.cfg.wwwroot+"/mod/bigbluebuttonbn/bbb_broker.php?"}),this.bigbluebuttonbn=t},waitModerator:function(){var t=e.one("#status_bar_span"),n=e.DOM.create("<img>");e.DOM.setAttribute(n,"id","spinning_wheel"),e.DOM.setAttribute(n,"src","pix/processing16.gif"),e.DOM.addHTML(t,"&nbsp;"),e.DOM.addHTML(t,n);var r="action=meeting_info";r+="&id="+this.bigbluebuttonbn.meetingid,r+="&bigbluebuttonbn="+this.bigbluebuttonbn.bigbluebuttonbnid,this.polling=this.datasource.setInterval(this.bigbluebuttonbn.ping_interval,{request:r,callback:{success:function(e){e.data.running&&(clearInterval(this.polling),M.mod_bigbluebuttonbn.rooms.clean_room(),M.mod_bigbluebuttonbn.rooms.update_room())},failure:function(){clearInterval(this.polling)}}})},join:function(t,n,r){var i="";if(!r){M.mod_bigbluebuttonbn.broker.joinRedirect(t);return}e.one("#panelContent").removeClass("hidden"),i+="action=meeting_info",i+="&id="+this.bigbluebuttonbn.meetingid,i+="&bigbluebuttonbn="+this.bigbluebuttonbn.bigbluebuttonbnid,this.datasource.sendRequest({request:i,callback:{success:function(n){if(!n.data.running){e.one("#meeting_join_url").set("value",t),e.one("#meeting_message").set("value",n.data.status.message),console.info("Something went wrong, the meeting is not running.");return}M.mod_bigbluebuttonbn.broker.join_redirect(t,n.data.status.message)}}})},join_redirect:function(e){window.open(e),setTimeout(function(){M.mod_bigbluebuttonbn.rooms.clean_room(),M.mod_bigbluebuttonbn.rooms.update_room()},15e3)},recording_action:function(e,t,n){console.info(e);if(e==="import"){this.recording_import(t);return}if(e==="delete"){this.recording_delete(t);return}if(e==="publish"){this.recording_publish(t,n);return}if(e==="unpublish"){this.recording_unpublish(t,n);return}},recording_import:function(t){var n=new M.core.confirm({modal:!0,centered:!0,question:this.recording_confirmation_message("import",t)});n.on("complete-yes",function(){this.datasource.sendRequest({request:"action=recording_import&id="+t,callback:{success:function(){e.one("#recording-td-"+t).remove()}}})},this)},recording_delete:function(t){var n=new M.core.confirm({modal:!0,centered:!0,question:this.recording_confirmation_message("delete",t)});n.on("complete-yes",function(){this.datasource.sendRequest({request:"action=recording_delete&id="+t,callback:{success:function(){e.one("#recording-td-"+t).remove()}}})},this)},recording_publish:function(e,t){this.recording_perform({action:"publish",recordingid:e,meetingid:t,goalstate:!0})},recording_unpublish:function(e,t){var n=new M.core.confirm({modal:!0,centered:!0,question:this.recording_confirmation_message("unpublish",e)});n.on("complete-yes",function(){this.recording_perform({action:"unpublish",recordingid:e,meetingid:t,goalstate:!1})},this)},recording_perform:function(e){M.mod_bigbluebuttonbn.recordings.recording_action_inprocess(e),this.datasource.sendRequest({request:"action=recording_"+e.action+"&id="+e.recordingid,callback:{success:function(t){if(t.data.status==="true")return M.mod_bigbluebuttonbn.broker.recording_action_performed({attempt:1,action:e.action,meetingid:e.meetingid,recordingid:e.recordingid,goalstate:e.goalstate});var n=new M.core.alert({message:t.data.message});return n.show(),M.mod_bigbluebuttonbn.recordings.recording_action_failed(e)},failure:function(){return M.mod_bigbluebuttonbn.recordings.recording_action_failed(e)}}})},recording_action_performed:function(e){console.info("Attempt "+e.attempt),this.datasource.sendRequest({request:"action=recording_info&id="+e.recordingid+"&idx="+e.meetingid,callback:{success:function(t){var n=t.data.published;return n===e.goalstate?M.mod_bigbluebuttonbn.recordings.recording_action_completed(e):e.attempt<5?(e.attempt+=1,setTimeout(function(){return function(){M.mod_bigbluebuttonbn.broker.recording_action_performed(e)}}(this),(e.attempt-1)*1e3)):M.mod_bigbluebuttonbn.recordings.recording_action_failed(e)},failure:function(){return M.mod_bigbluebuttonbn.recordings.recording_action_failed(e)}}})},recording_confirmation_message:function(t,n){if(M.mod_bigbluebuttonbn.locales.strings[t+"_confirmation"]==="undefined")return"";var r=e.one("#playbacks-"+n).get("dataset").imported==="true",i=M.mod_bigbluebuttonbn.locales.strings.recording;r&&(i=M.mod_bigbluebuttonbn.locales.strings.recording_link);var s=M.mod_bigbluebuttonbn.locales.strings[t+"_confirmation"];s=s.replace("{$a}",i);if(t==="publish"||t==="delete"){var o=e.one("#recording-link-"+t+"-"+n).get("dataset").links,u=M.mod_bigbluebuttonbn.locales.strings[t+"_confirmation_warning_p"];o==1&&(u=M.mod_bigbluebuttonbn.locales.strings[t+"_confirmation_warning_s"]),u=u.replace("{$a}",o)+". ",s=u+"\n\n"+s}return s},end_meeting:function(){var e="action=meeting_end&id="+this.bigbluebuttonbn.meetingid;e+="&bigbluebuttonbn="+this.bigbluebuttonbn.bigbluebuttonbnid,this.datasource.sendRequest({request:e,callback:{success:function(e){e.data.status&&(M.mod_bigbluebuttonbn.rooms.clean_control_panel(),M.mod_bigbluebuttonbn.rooms.hide_join_button(),M.mod_bigbluebuttonbn.rooms.hide_end_button(),location.reload())}}})}}},"@VERSION@",{requires:["base","node","datasource-get","datasource-jsonschema","datasource-polling","moodle-core-notification"]});
