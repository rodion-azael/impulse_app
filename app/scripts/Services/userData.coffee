userData = ($http, $q) ->

  user = {
    user_id: '318'
    client_id: '132'
    type: '3' 
    nickname: 'prueba 1'
    username: 'prueba 1'
    email: '3@makrosoft.com'
  }

  # ----- #
  userCanByType = (type) ->
    defered = $q.defer()
    obj = 
      type: type
      user: user.client_id

    $http.post(config.Url + 'userCanByType/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise

  # ----- #
  isLogged = () ->
    true
  
  return {
    User: user
    IsLogged: isLogged()
    UserCanByType: userCanByType
  }

angular
	.module('appAgenda')
	.factory('UserData', userData)
userData
  .$inject = ['$http', '$q']