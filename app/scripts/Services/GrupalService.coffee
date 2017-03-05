GrupalService = ($http, $q) ->

  getDaySchedule = (obj) ->
    defered = $q.defer()
    $http.post(config.Url + 'getGroupSessions/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise

  addToSesion = (obj) ->
    defered = $q.defer()

    $http.post(config.Url + 'addSesion/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise

  userIsOnGroup = (obj) -> 
    defered = $q.defer()

    $http.post(config.Url + 'userIsOnGroup/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise
  
  # ---- #
  return {
    GetDaySchedule: getDaySchedule
    UserIsOnGroup: userIsOnGroup
    AddToSesion: addToSesion
  }

angular
	.module('appAgenda')
	.service('GrupalService', GrupalService)
GrupalService
  .$inject = ['$http', '$q']