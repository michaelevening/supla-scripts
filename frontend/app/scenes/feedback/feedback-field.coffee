angular.module('supla-scripts').component 'feedbackField',
  templateUrl: 'app/scenes/feedback/feedback-field.html'
  require:
    ngModel: 'ngModel'
  controller: (Channels, channelLabelFilter, $timeout, $element) ->
    CHANNEL_FEEDBACKS =
      FNC_LIGHTSWITCH: [{display: 'zaświecone/zgaszone', suffix: 'on|bool:zaświecone,zgaszone'}]
      FNC_POWERSWITCH: [{display: 'włączone/wyłączone', suffix: 'on|bool:włączone,wyłączone'}]
      FNC_THERMOMETER: [
        {display: 'temperatura', suffix: 'temperature|number:1'}
        {display: 'warunek temperatury', suffix: 'temperature|compare:<,10,zimno,ciepło'}
      ]
      FNC_HUMIDITYANDTEMPERATURE: [
        {display: 'temperatura', suffix: 'temperature|number:1'},
        {display: 'warunek temperatury', suffix: 'temperature|compare:<,10,zimno,ciepło'}
        {display: 'wilgotność', suffix: 'humidity|number:0'}
        {display: 'warunek wilgotności', suffix: 'humidity|compare:<,50,sucho,wilgotno'}
      ]
      FNC_OPENINGSENSOR_GARAGEDOOR: [{display: 'otwarta/zamknięta', suffix: 'hi|bool:zamknięta,otwarta'}]
      FNC_OPENINGSENSOR_DOOR: [{display: 'otwarte/zamknięte', suffix: 'hi|bool:zamknięte,otwarte'}]
      FNC_OPENINGSENSOR_ROLLERSHUTTER: [{display: 'otwarte/zamknięte', suffix: 'hi|bool:zamknięte,otwarte'}]
      FNC_OPENINGSENSOR_GATE: [{display: 'otwarta/zamknięta', suffix: 'hi|bool:zamknięta,otwarta'}]
      FNC_OPENINGSENSOR_GATEWAY: [{display: 'otwarta/zamknięta', suffix: 'hi|bool:zamknięta,otwarta'}]
      FNC_OPENINGSENSOR_WINDOW: [{display: 'otwarte/zamknięte', suffix: 'hi|bool:zamknięte,otwarte'}]
      FNC_MAILSENSOR: [{display: 'jest/nie ma', suffix: 'hi|bool:nie ma,jest'}]

    new class
      $onInit: ->
        @text = ''
        @ngModel.$render = => @text = @ngModel.$viewValue or ''
        Channels.getList(Object.keys(CHANNEL_FEEDBACKS)).then (@feedbackableChannels) =>
        @config =
          autocomplete: []
          dropdown: [
            {
              trigger: /\{([^\s]*)/ig
              list: (match, callback) =>
                availableFeedbacks = []
                if @feedbackableChannels
                  availableFeedbacks = @flatten @feedbackableChannels.map (channel) ->
                    angular.copy(CHANNEL_FEEDBACKS[channel.function.name]).map (feedback) ->
                      feedback.display = channelLabelFilter(channel) + " (#{feedback.display})"
                      feedback.channel = channel
                      feedback
                callback availableFeedbacks.filter (feedback) ->
                  !match[0] or feedback.display.toLocaleLowerCase().indexOf(match[1].toLowerCase()) >= 0
              onSelect: (item) =>
                $timeout(@onChange)
                "{#{item.channel.id}|#{item.suffix}}}"
              mode: 'replace'
            }
          ]
        $timeout(-> $element.find('textarea').keyup())

      flatten: (arrayOfArrays) ->
        [].concat.apply([], arrayOfArrays)

      onChange: =>
        @ngModel.$setViewValue(@text)
