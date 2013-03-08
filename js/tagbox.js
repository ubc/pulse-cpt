/*
* Based on the work by Daniel Stocks
* jQuery tagbox
* -------------
* Released under the MIT, BSD, and GPL Licenses.
* Copyright 2011 Daniel Stocks
*
* Dependencies:
* ------------
* jquery.autoGrowInput.js
*
*/
(function($) {
	
    function TagBox(input, options) {
        var self = this;
        
        self.options = options;
        self.delimit_key = 188;
        self.delimit_expr = /\s*,\s*/;
		
        if ( options.delimit_by_space ) {
            self.delimit_key = 32;
            self.delimit_expr = /\s+/;
        }
		
        var val = input.val();
        var tags = [];
        if ( val ) {
            tags = input.val().split(self.delimit_expr);
        }
		
        self.input = input;
        self.tagInput = $('<input>', {
            'type': 'text',
			'placeholder': input.attr('placeholder'),
			'width': 240,
            'keydown': function(e) {
                if ( e.keyCode == 13 || e.keyCode == self.delimit_key ) {
                    $(this).trigger("selectTag");
                    e.preventDefault();
                }
            },
            'blur': function(e) {
                $(this).val("");
            }
        });
        
        self.tagInput.bind( "selectTag", function() {
            if ( ! $(this).val() ) return;
			
            self.addTag($(this).val());
            $(this).val("");
        });
        
        self.tagbox = $('<ul>', {
            'class': "tagbox",
            'click': function(e) {
                self.tagInput.focus();
            }
        });
		
        self.tags = {}
        
        input.after(self.tagbox).hide();
		
        self.inputHolder = $('<li class="input">');
        self.tagbox.append(self.inputHolder);
        self.inputHolder.append(self.tagInput);
        //self.tagInput.autoGrowInput();
        
        for ( tag in tags ) {
            self.addTag(tags[tag]);
        }
    }
    
    TagBox.prototype = {
		
        addTag: function(label) {
			if ( ! this.tags.hasOwnProperty(label) ) {
				var self = this;
				var tag = $('<li class="tag">' + $('<div>').text(label).remove().html() + '</li>');
				
				this.tags[label] = true;
				
				tag.append($('<a>', {
					"href" : "#",
					"class": "close",
					"text": "close",
					click: function(e) {
						e.preventDefault();
						var index = self.tagbox.find("li").index($(this).parent());
						self.removeTag(index);
					},
				}));
				
				this.inputHolder.before(tag);
				this.updateInput();
			}
        },
		
        removeTag: function(index) {
            this.tagbox.find("li").eq(index).remove();
            delete this.tags[index];
            this.updateInput();
        },
		
        updateInput: function() {
            var tags;
            if (this.options.delimit_by_space) {
				tags = this.getTags().join(" ");
            } else {
				tags = this.getTags().join(",");
			}
			
            this.input.val(tags);
        },
		
		getTags: function() {
			var tags = [];
			for ( var tag in this.tags ) {
				if (this.tags.hasOwnProperty(tag)) { //to be safe
					tags.push(tag);
				}
			}
			
			return tags;
		}
    }
    
    $.fn.tagBox = function(options) {
        var defaults = {
            delimit_by_space : false
        };
		
        options = $.extend(defaults, options);
		
        return this.each(function() {
            var input = $(this);
            var tagbox = new TagBox(input, options);
        });
    }
	
})(jQuery);

