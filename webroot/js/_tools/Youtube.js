define(['//www.youtube.com/player_api'], function() {
	var params = {
		
		Implements: [Options, Events],
		
		options: {
			videoId: null,
			element: null,
			height:  270,
			width:   475
		},
		
		player: null,
		
		initialize: function(options) {
			this.setOptions(options);
	
			window.onYouTubePlayerAPIReady = this.initPlayer.bind(this);
		},
		
		initPlayer: function() {
			var self = this;
			
			this.player = new YT.Player(this.options.element, {
				height:  this.options.height,
				width:   this.options.width,
				videoId: this.options.videoId,
				events:  {
					onReady:       function(e) {
						self.fireEvent.bind(self, ['ready', [e]])();
					},
					onStateChange: function(e) {
						self.fireEvent.bind(self, ['stateChange', [e]])();
					}
				}
			});
		},
		
		playVideo: function() {
			return this.player.playVideo();
		}
	};
	
	return new Class(params);
});