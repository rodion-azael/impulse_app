SemiService = ($http, $q) ->

  # ---- #
  validateCode = (obj) ->
    defered = $q.defer()

    $http.post(config.Url + 'validateCode/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data
        return
    defered.promise

  # ---- #
  validateIfUserIsOnIt = (id) ->
    defered = $q.defer()
    $http.get(config.Url + 'userIsOnSession/' + id)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise


  # ---- Add a private sesion #
  addSesion = (obj) ->
    defered = $q.defer()

    $http.post(config.Url + 'addSesion/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data.data        
        return
    defered.promise
  
  # ---- #
  return {
    ValidateCode: validateCode
    UserName: 'cliente 2'
    AddSesion: addSesion
    ValidateIfUserIsOnIt: validateIfUserIsOnIt
  }

angular
	.module('appAgenda')
	.service('SemiService', SemiService)
SemiService
  .$inject = ['$http', '$q']