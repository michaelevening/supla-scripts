angular.module('supla-scripts').component 'sceneSelector',
  templateUrl: 'app/scenes/selector/scene-selector.html'
  bindings:
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: (Channels, $scope, $q, CHANNEL_AVAILABLE_ACTIONS) ->
    new class
      scene: []

      $onInit: ->
        @sceneableFunctions = Object.keys(CHANNEL_AVAILABLE_ACTIONS)
        @ngModel.$render = =>
          sceneStrings = (@ngModel.$viewValue or '').split('|').filter((e) -> !!e)
          promises = sceneStrings.map((sceneString) -> Channels.get(sceneString.split(';')[0]))
          @loadingChannels = yes
          $q.all(promises).then (channels) =>
            @loadingChannels = no
            @scene = sceneStrings.map (sceneString, index) =>
              channel: channels[index]
              action: sceneString.split(';')[1]
        $scope.$watch '$ctrl.scene.length', =>
          @usedChannelIds = @scene.map((o) -> o.channel.id)
          @onChange()

      addNewChannelToScene: (newChannelId) ->
        if (newChannelId)
          Channels.get(newChannelId).then (channel) =>
            @scene.push({channel})

      onChange: ->
        if not @disabled
          operationsWithActions = @scene.filter((operation) -> !!operation.action)
          sceneString = operationsWithActions.map((operation) -> "#{operation.channel.id};#{operation.action}").join('|')
          @ngModel.$setViewValue(sceneString)
