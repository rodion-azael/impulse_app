addToSemiCtrl = ($state, User, SemiService) ->
  vm = @

  # ---- #
  init = () ->
    do validateIfUserIsOnIt
    return

  # ---- #
  validateIfUserIsOnIt = () ->
    id = $state.params.id
    SemiService.ValidateIfUserIsOnIt(id)
      .then (data) ->
        vm.Loading = false
        if data.success == 'true' then vm.OnIt = true
        vm.UsersOnSession = data.obj
    return

  # ---- #
  validate = () ->
    vm.ErrorMsg = false
    obj = {
      code: vm.code
      id: $state.params.id
    }
    SemiService.ValidateCode(obj)
      .then (data) ->
        if data.data.success == "false" then vm.ErrorMsg = true
    return

  # ---- #
  vm.validate = validate
  vm.init = init

  vm.code = '0000'
  vm.ErrorMsg = false
  vm.Loading = true
  vm.OnIt = false
  vm.UsersOnSession = {}

  do vm.init
  return

angular
  .module('appAgenda')
  .controller('addToSemiCtrl', addToSemiCtrl)

addToSemiCtrl
  .$inject = ['$state', 'UserData', 'SemiService']