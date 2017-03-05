var config;

config = {
  Url: 'http://localhost:8080/api/impulse/',
  post: {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  }
};

(function() {
  angular.module('appAgenda', ['ui.router', 'mwl.calendar', 'ui.bootstrap']).config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise("/inicio");
    $stateProvider.state('main', {
      url: '/inicio',
      templateUrl: 'partials/view1.html',
      controller: 'agendaCtrl',
      controllerAs: 'vm'
    }).state('private', {
      url: '/privado',
      templateUrl: 'partials/private.html',
      controller: 'agendaPrivateCtrl',
      controllerAs: 'vm'
    }).state('semi', {
      url: '/semi-grupal',
      templateUrl: 'partials/semi.html',
      controller: 'agendaSemiCtrl',
      controllerAs: 'vm'
    }).state('addToSemi', {
      url: '/agregar/:id',
      templateUrl: 'partials/semi-include.html',
      controller: 'addToSemiCtrl',
      controllerAs: 'vm',
      params: {
        id: null,
        obj: {}
      }
    }).state('grupal', {
      url: '/grupal',
      templateUrl: 'partials/grupal.html',
      controller: 'agendaGrupalCtrl',
      controllerAs: 'vm'
    }).state('grupal-add', {
      url: '/grupal-add',
      templateUrl: 'partials/grupalAdd.html',
      controller: 'agendaGrupalAddCtrl',
      controllerAs: 'vm',
      params: {
        span: null
      }
    });
  }).run(function() {
    moment.defineLocale('es', {
      parentLocale: 'en',
      monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      weekdays: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
      weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab']
    });
  });
})();

var addToSemiCtrl;

addToSemiCtrl = function($state, User, SemiService) {
  var init, validate, validateIfUserIsOnIt, vm;
  vm = this;
  init = function() {
    validateIfUserIsOnIt();
  };
  validateIfUserIsOnIt = function() {
    var id;
    id = $state.params.id;
    SemiService.ValidateIfUserIsOnIt(id).then(function(data) {
      vm.Loading = false;
      if (data.success === 'true') {
        vm.OnIt = true;
      }
      return vm.UsersOnSession = data.obj;
    });
  };
  validate = function() {
    var obj;
    vm.ErrorMsg = false;
    obj = {
      code: vm.code,
      id: $state.params.id
    };
    SemiService.ValidateCode(obj).then(function(data) {
      if (data.data.success === "false") {
        return vm.ErrorMsg = true;
      }
    });
  };
  vm.validate = validate;
  vm.init = init;
  vm.code = '0000';
  vm.ErrorMsg = false;
  vm.Loading = true;
  vm.OnIt = false;
  vm.UsersOnSession = {};
  vm.init();
};

angular.module('appAgenda').controller('addToSemiCtrl', addToSemiCtrl);

addToSemiCtrl.$inject = ['$state', 'UserData', 'SemiService'];

var agendaCtrl;

agendaCtrl = function($state, User, Center) {
  var getCabinas, init, redirectByType, typeChoseed, vm;
  vm = this;
  typeChoseed = function() {
    return User.UserCanByType(vm.selectedType).then(function(data) {
      if (data.success === "true") {
        Center.SelectCabina(vm.selectedType);
        return redirectByType();
      } else {
        return alert(data.message);
      }
    });
  };
  redirectByType = function() {
    var goTo;
    goTo = "main";
    if (vm.selectedType === "24") {
      goTo = "private";
    } else if (vm.selectedType === "26") {
      goTo = 'semi';
    } else if (vm.selectedType === "25") {
      goTo = 'grupal';
    }
    $state.go(goTo);
  };
  getCabinas = function() {
    Center.GetCabinas(1).then(function(data) {
      vm.Cabinas = data;
    });
  };
  init = function() {
    if (User.IsLogged === false) {
      alert('No ha iniciado sesion');
      return false;
    }
    getCabinas();
  };
  vm.init = init;
  vm.selectedType = '';
  vm.typeChoseed = typeChoseed;
  vm.init();
};

angular.module('appAgenda').controller('agendaCtrl', agendaCtrl);

agendaCtrl.$inject = ['$state', 'UserData', 'CenterDataService'];

var agendaGrupalCtrl;

agendaGrupalCtrl = function($state, User, GrupalService) {
  var addToGroupSesion, changeDate, dataToCalendar, getCabinaDetails, init, openCalendar, vm;
  vm = this;
  openCalendar = function() {
    vm.calendarOpened = true;
  };
  changeDate = function() {
    vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY');
    getCabinaDetails();
  };
  dataToCalendar = function() {
    var app, hourEnd, hourStart, i, len, minEnd, minStart, ref;
    vm.events = [];
    ref = vm.Cabina;
    for (i = 0, len = ref.length; i < len; i++) {
      app = ref[i];
      hourStart = app.start.substring(0, 2);
      minStart = app.start.substring(3, 5);
      hourEnd = app.end.substring(0, 2);
      minEnd = app.end.substring(3, 5);
      vm.events.push({
        id: app.id,
        title: app.title + ' -- Disponibles: ' + app.available,
        startsAt: new Date(vm.appDate).setHours(parseInt(hourStart), parseInt(minStart)),
        endsAt: new Date(vm.appDate).setHours(parseInt(hourEnd), parseInt(minEnd)),
        draggable: false
      });
    }
    vm.doneLoading = true;
  };
  addToGroupSesion = function(timespan) {
    $state.go('grupal-add', {
      span: timespan
    });
  };
  getCabinaDetails = function() {
    var obj;
    obj = {
      day: moment(vm.appDate).day(),
      date: moment(vm.appDate).format('YYYY-MM-DD'),
      cbn: '25'
    };
    GrupalService.GetDaySchedule(obj).then(function(data) {
      vm.Cabina = data.obj;
      dataToCalendar();
    });
  };
  init = function() {
    if (User.IsLogged === false) {
      alert('No ha iniciado sesion');
      return false;
    }
    getCabinaDetails();
  };
  vm.init = init;
  vm.calendarOpened = false;
  vm.appDate = new Date();
  vm.openCalendar = openCalendar;
  vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY');
  vm.changeDate = changeDate;
  vm.Cabina = {};
  vm.events = [];
  vm.doneLoading = false;
  vm.addToGroupSesion = addToGroupSesion;
  vm.calendarView = "day";
  vm.Apertura = '08:00:00';
  vm.Cierre = '19:00:00';
  vm.init();
};

angular.module('appAgenda').controller('agendaGrupalCtrl', agendaGrupalCtrl);

agendaGrupalCtrl.$inject = ['$state', 'UserData', 'GrupalService'];

var agendaGrupalAddCtrl;

agendaGrupalAddCtrl = function($state, User, GrupalService) {
  var UserIsOnGroup, addTosession, init, vm;
  vm = this;
  init = function() {
    return vm.UserIsOnGroup();
  };
  UserIsOnGroup = function() {
    var obj;
    obj = {
      start: vm.start,
      cbn: '25'
    };
    GrupalService.UserIsOnGroup(obj).then(function(data) {
      if (data.success === "true") {
        vm.UserIsOnSession = true;
      }
      return vm.Loading = false;
    });
  };
  addTosession = function() {
    var obj;
    obj = {
      start: moment($state.params.span.startsAt).seconds(0).format('YYYY-MM-DD HH:mm:ss'),
      end: moment($state.params.span.startsAt).seconds(0).add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss'),
      DURACION_CITA: 30,
      user: User.User.client_id,
      type: '25'
    };
    GrupalService.AddToSesion(obj).then(function(data) {
      if (data.success === true) {
        return vm.UserIsOnSession = true;
      }
    });
  };
  vm.start = moment($state.params.span.startsAt).seconds(0).format('YYYY-MM-DD HH:mm:ss');
  vm.UserIsOnSession = false;
  vm.Loading = true;
  vm.title = $state.params.span.title;
  vm.init = init;
  vm.UserIsOnGroup = UserIsOnGroup;
  vm.addTosession = addTosession;
  vm.init();
};

angular.module('appAgenda').controller('agendaGrupalAddCtrl', agendaGrupalAddCtrl);

agendaGrupalAddCtrl.$inject = ['$state', 'UserData', 'GrupalService'];

var agendaPrivateCtrl;

agendaPrivateCtrl = function(UserData, PrivateService, Center) {
  var changeDate, dataToCalendar, getCabinaDetails, init, newPrivateSesion, openCalendar, vm;
  vm = this;
  openCalendar = function() {
    vm.calendarOpened = true;
  };
  changeDate = function() {
    vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY');
    getCabinaDetails();
  };
  newPrivateSesion = function(timespan) {
    var obj;
    if (confirm('¿Desea agendar una sesión para el dia ' + timespan.format('DD MMM YYYY') + ' a las ' + timespan.format('hh:mm') + '?')) {
      obj = {
        start: timespan.format('YYYY-MM-DD HH:mm:ss'),
        end: timespan.add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss'),
        DURACION_CITA: 30,
        user: UserData.User.client_id,
        type: '24'
      };
      PrivateService.AddSesion(obj).then(function(data) {
        getCabinaDetails();
      });
    }
  };
  dataToCalendar = function() {
    var app, i, len, ref;
    vm.events = [];
    ref = vm.Cabina.Appnmts;
    for (i = 0, len = ref.length; i < len; i++) {
      app = ref[i];
      vm.events.push({
        id: app.id,
        title: app.title,
        user_id: app.user_id,
        startsAt: new Date(app.start),
        endsAt: new Date(app.end),
        draggable: false
      });
    }
    vm.doneLoading = true;
  };
  getCabinaDetails = function() {
    var cabinaID, date;
    cabinaID = Center.SelectedCabina();
    date = moment(vm.appDate).format('YYYY/MM/DD');
    Center.GetCabinaDetails(cabinaID, date).then(function(data) {
      vm.Cabina = data.data;
      dataToCalendar();
    });
  };
  init = function() {
    if (UserData.IsLogged === false) {
      alert('No ha iniciado sesion');
      return false;
    }
    getCabinaDetails();
  };
  vm.init = init;
  vm.calendarOpened = false;
  vm.appDate = new Date();
  vm.openCalendar = openCalendar;
  vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY');
  vm.changeDate = changeDate;
  vm.newPrivateSesion = newPrivateSesion;
  vm.Cabina = {};
  vm.events = [];
  vm.doneLoading = false;
  vm.calendarView = "day";
  vm.Apertura = '08:00:00';
  vm.Cierre = '19:00:00';
  vm.init();
};

angular.module('appAgenda').controller('agendaPrivateCtrl', agendaPrivateCtrl);

agendaPrivateCtrl.$inject = ['UserData', 'PrivateService', 'CenterDataService'];

var agendaSemiCtrl;

agendaSemiCtrl = function($state, User, Center, SemiService) {
  var changeDate, dataToCalendar, getCabinaDetails, includeInSemi, init, newSemiSesion, openCalendar, vm;
  vm = this;
  openCalendar = function() {
    vm.calendarOpened = true;
  };
  changeDate = function() {
    vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY');
    getCabinaDetails();
  };
  dataToCalendar = function() {
    var app, i, len, ref;
    vm.events = [];
    ref = vm.Cabina.Appnmts;
    for (i = 0, len = ref.length; i < len; i++) {
      app = ref[i];
      vm.events.push({
        id: app.id,
        title: app.title,
        user_id: app.user_id,
        startsAt: new Date(app.start),
        endsAt: new Date(app.end),
        draggable: false
      });
    }
    vm.doneLoading = true;
  };
  getCabinaDetails = function() {
    var cabinaID, date;
    cabinaID = Center.SelectedCabina();
    date = moment(vm.appDate).format('YYYY/MM/DD');
    Center.GetCabinaDetails(cabinaID, date).then(function(data) {
      vm.Cabina = data.data;
      vm.Apertura = vm.Cabina.Details[0].Apertura;
      vm.Cierre = vm.Cabina.Details[0].Cierre;
      dataToCalendar();
    });
  };
  includeInSemi = function(event) {
    $state.go('addToSemi', {
      id: event.id
    });
  };
  newSemiSesion = function(timespan) {
    var obj;
    if (confirm('¿Desea agendar una sesión para el dia ' + timespan.format('DD MMM YYYY') + ' a las ' + timespan.format('hh:mm') + '?')) {
      obj = {
        start: timespan.format('YYYY-MM-DD HH:mm:ss'),
        end: timespan.add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss'),
        DURACION_CITA: 30,
        user: User.User.client_id,
        type: '26'
      };
      SemiService.AddSesion(obj).then(function(data) {
        alert('Se ha creado la sesión. Utiliza el código ' + data.code + ' para invitar a mas personas a esta sesión.');
        getCabinaDetails();
      });
    }
  };
  init = function() {
    if (User.IsLogged === false) {
      alert('No ha iniciado sesion');
      return false;
    }
    getCabinaDetails();
  };
  vm.init = init;
  vm.calendarOpened = false;
  vm.appDate = new Date();
  vm.openCalendar = openCalendar;
  vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY');
  vm.changeDate = changeDate;
  vm.Cabina = {};
  vm.events = [];
  vm.doneLoading = false;
  vm.newSemiSesion = newSemiSesion;
  vm.includeInSemi = includeInSemi;
  vm.calendarView = "day";
  vm.Apertura = '08:00:00';
  vm.Cierre = '19:00:00';
  vm.init();
};

angular.module('appAgenda').controller('agendaSemiCtrl', agendaSemiCtrl);

agendaSemiCtrl.$inject = ['$state', 'UserData', 'CenterDataService', 'SemiService'];

var CenterDataService;

CenterDataService = function($http, $q) {
  var getCabinaDetails, getCabinas, getCentros, getSelectedCabina, selectCabina, selectedCabina, vm;
  vm = this;
  selectedCabina = '';
  getCentros = function() {
    return {
      '1': 'MIAMI'
    };
  };
  getSelectedCabina = function() {
    return vm.selectedCabina;
  };
  selectCabina = function(id) {
    vm.selectedCabina = id;
  };
  getCabinas = function(id) {
    var defered;
    defered = $q.defer();
    $http.get(config.Url + 'getCabinas/' + id).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  getCabinaDetails = function(id, date) {
    var defered;
    defered = $q.defer();
    $http.get(config.Url + 'getCabinaDetails/' + id + '/' + date + '/').then(function(data) {
      defered.resolve(data);
    });
    return defered.promise;
  };
  vm.selectedCabina = selectedCabina;
  return {
    GetCabinas: getCabinas,
    SelectCabina: selectCabina,
    SelectedCabina: getSelectedCabina,
    GetCabinaDetails: getCabinaDetails
  };
};

angular.module('appAgenda').factory('CenterDataService', CenterDataService);

CenterDataService.$inject = ['$http', '$q'];

var GrupalService;

GrupalService = function($http, $q) {
  var addToSesion, getDaySchedule, userIsOnGroup;
  getDaySchedule = function(obj) {
    var defered;
    defered = $q.defer();
    $http.post(config.Url + 'getGroupSessions/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  addToSesion = function(obj) {
    var defered;
    defered = $q.defer();
    $http.post(config.Url + 'addSesion/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  userIsOnGroup = function(obj) {
    var defered;
    defered = $q.defer();
    $http.post(config.Url + 'userIsOnGroup/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  return {
    GetDaySchedule: getDaySchedule,
    UserIsOnGroup: userIsOnGroup,
    AddToSesion: addToSesion
  };
};

angular.module('appAgenda').service('GrupalService', GrupalService);

GrupalService.$inject = ['$http', '$q'];

var PrivateService;

PrivateService = function($http, $q) {
  var addSesion, getServicesAvailable;
  getServicesAvailable = function() {
    return [];
  };
  addSesion = function(obj) {
    var defered;
    defered = $q.defer();
    $http.post(config.Url + 'addSesion/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  return {
    UserName: 'cliente 2',
    GetServicesAvailable: getServicesAvailable,
    AddSesion: addSesion
  };
};

angular.module('appAgenda').service('PrivateService', PrivateService);

PrivateService.$inject = ['$http', '$q'];

var SemiService;

SemiService = function($http, $q) {
  var addSesion, validateCode, validateIfUserIsOnIt;
  validateCode = function(obj) {
    var defered;
    defered = $q.defer();
    $http.post(config.Url + 'validateCode/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data);
    });
    return defered.promise;
  };
  validateIfUserIsOnIt = function(id) {
    var defered;
    defered = $q.defer();
    $http.get(config.Url + 'userIsOnSession/' + id).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  addSesion = function(obj) {
    var defered;
    defered = $q.defer();
    $http.post(config.Url + 'addSesion/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  return {
    ValidateCode: validateCode,
    UserName: 'cliente 2',
    AddSesion: addSesion,
    ValidateIfUserIsOnIt: validateIfUserIsOnIt
  };
};

angular.module('appAgenda').service('SemiService', SemiService);

SemiService.$inject = ['$http', '$q'];

var userData;

userData = function($http, $q) {
  var isLogged, user, userCanByType;
  user = {
    user_id: '318',
    client_id: '132',
    type: '3',
    nickname: 'prueba 1',
    username: 'prueba 1',
    email: '3@makrosoft.com'
  };
  userCanByType = function(type) {
    var defered, obj;
    defered = $q.defer();
    obj = {
      type: type,
      user: user.client_id
    };
    $http.post(config.Url + 'userCanByType/', $.param(obj), config.post).then(function(data) {
      defered.resolve(data.data);
    });
    return defered.promise;
  };
  isLogged = function() {
    return true;
  };
  return {
    User: user,
    IsLogged: isLogged(),
    UserCanByType: userCanByType
  };
};

angular.module('appAgenda').factory('UserData', userData);

userData.$inject = ['$http', '$q'];

