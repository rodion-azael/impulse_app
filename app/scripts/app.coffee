config = {
	Url : 'http://localhost:8080/api/impulse/'
	post:
		headers: 
			'Content-Type': 'application/x-www-form-urlencoded' 
}

do () ->
	angular
		.module('appAgenda', [
			'ui.router',
			'mwl.calendar',
			'ui.bootstrap'
		])
		.config  ($stateProvider, $urlRouterProvider) ->
			$urlRouterProvider.otherwise "/inicio"
			$stateProvider
				.state 'main',
					url: '/inicio'
					templateUrl: 'partials/view1.html'
					controller: 'agendaCtrl'
					controllerAs: 'vm'
				.state 'private',
					url: '/privado'
					templateUrl: 'partials/private.html'
					controller: 'agendaPrivateCtrl'
					controllerAs: 'vm'
				.state 'semi',
					url: '/semi-grupal'
					templateUrl: 'partials/semi.html'
					controller: 'agendaSemiCtrl'
					controllerAs: 'vm'
				.state 'addToSemi',
					url: '/agregar/:id'
					templateUrl: 'partials/semi-include.html'
					controller: 'addToSemiCtrl'
					controllerAs: 'vm'
					params:
						id: null
						obj: {}
				.state 'grupal',
					url: '/grupal'
					templateUrl: 'partials/grupal.html'
					controller: 'agendaGrupalCtrl'
					controllerAs: 'vm'
				.state 'grupal-add',
					url: '/grupal-add'
					templateUrl: 'partials/grupalAdd.html'
					controller: 'agendaGrupalAddCtrl'
					controllerAs: 'vm'
					params:
						span: null
			return
		.run () ->
			moment.defineLocale 'es', {
			  parentLocale: 'en'
			  monthsShort : ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
			  months : ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
			  weekdays : ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado']
			  weekdaysShort : ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab']
			};
			return		
	return