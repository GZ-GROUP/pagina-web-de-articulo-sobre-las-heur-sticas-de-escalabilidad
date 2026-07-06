<?php
$heuristicas_principales = [
    [
        "id" => "H01",
        "titulo" => "Usar caché para datos de acceso frecuente",
        "categoria" => "Caché",
        "frecuencia" => 47.1,
        "fuentes" => 8,
        "consenso" => "Alto",
        "descripcion" => "Almacenar temporalmente los resultados de operaciones costosas o datos consultados con frecuencia en una capa de caché (como Redis o Memcached) para evitar recalcularlos o recuperarlos de la base de datos en cada solicitud.",
        "detalle" => "La base de datos es típicamente el cuello de botella más común en aplicaciones web bajo carga. Cada vez que un usuario solicita los mismos datos, ejecutar una consulta completa desperdicia tiempo de CPU, I/O de disco y conexiones de red. La caché resuelve esto guardando el resultado en memoria por un tiempo determinado, de modo que las solicitudes siguientes se resuelven en microsegundos en lugar de milisegundos. Se aplica a resultados de consultas frecuentes, sesiones de usuario, datos de configuración, respuestas de APIs externas y cualquier dato que cambie poco pero se lea mucho. La clave está en definir una política de invalidación adecuada: ¿cuándo caduca el dato? ¿Se invalida por tiempo (TTL), por evento (al actualizar el registro), o ambos? Una caché mal configurada puede servir datos obsoletos, por lo que la estrategia de invalidación es tan importante como la de almacenamiento.",
        "ejemplo" => "Un perfil de usuario se consulta en cada página. En lugar de hacer SELECT * FROM users WHERE id=? en cada petición, se guarda el resultado en Redis con una clave user:123 y un TTL de 10 minutos. El 95% de las peticiones se resuelven desde memoria sin tocar la base de datos.",
        "academicas" => 3,
        "industriales" => 5
    ],
    [
        "id" => "H02",
        "titulo" => "Usar CDN o edge computing para servir contenido cerca del usuario",
        "categoria" => "Caché",
        "frecuencia" => 47.1,
        "fuentes" => 8,
        "consenso" => "Alto",
        "descripcion" => "Distribuir los activos estáticos (imágenes, CSS, JS, videos) y en algunos casos respuestas de API a través de una red de servidores geográficamente distribuidos (CDN), de modo que cada usuario recibe el contenido desde el nodo más cercano a su ubicación.",
        "detalle" => "La latencia de red es proporcional a la distancia física entre el usuario y el servidor. Si tu servidor está en Virginia y tu usuario está en Tokio, cada solicitud tiene un viaje de ida y vuelta de cientos de milisegundos solo por la velocidad de la luz. Un CDN replica tus activos en docenas o cientos de puntos de presencia (PoPs) alrededor del mundo, de modo que el usuario en Tokio recibe la imagen desde un servidor en Osaka. Esto reduce la latencia percibida, descarga al servidor de origen de servir archivos estáticos (que pueden representar el 70-80% de las solicitudes totales), y mejora la disponibilidad porque el contenido sigue accesible aunque el servidor de origen tenga problemas. Servicios como Cloudflare, AWS CloudFront o Fastly permiten configurar esto con cambios mínimos en el código de la aplicación.",
        "ejemplo" => "Netflix desplegó su propia CDN (Open Connect) dentro de los ISPs para eliminar el tráfico de internet público. Esto le permite servir miles de millones de horas de video al mes con latencia mínima y sin depender de CDNs de terceros.",
        "academicas" => 2,
        "industriales" => 6
    ],
    [
        "id" => "H03",
        "titulo" => "Implementar logging y trazabilidad distribuida",
        "categoria" => "Monitoreo y observabilidad",
        "frecuencia" => 35.3,
        "fuentes" => 6,
        "consenso" => "Alto",
        "descripcion" => "Registrar sistemáticamente los eventos del sistema y correlacionar esos registros con un identificador único de solicitud que atraviese todos los servicios involucrados en procesarla, permitiendo reconstruir el camino completo de cualquier petición.",
        "detalle" => "En un sistema distribuido, una solicitud del usuario puede pasar por un balanceador de carga, un servidor web, tres microservicios distintos y dos bases de datos antes de devolver una respuesta. Cuando algo falla o es lento, saber exactamente dónde ocurrió el problema es imposible sin trazabilidad. El logging registra qué pasó; el tracing correlaciona esos eventos bajo un mismo ID. Herramientas como OpenTelemetry, Jaeger o Zipkin permiten visualizar el árbol completo de una solicitud, ver cuánto tardó cada servicio, y detectar el servicio responsable de una degradación. Sin esto, depurar problemas en producción se convierte en trabajo de adivinanza. Esta heurística no es solo para cuando las cosas fallan: los traces también revelan ineficiencias latentes que no producen errores pero sí degradan la experiencia del usuario.",
        "ejemplo" => "Una solicitud llega al API gateway en 800ms cuando debería tardar 50ms. Con distributed tracing se descubre que el microservicio de inventario está llamando a la base de datos 47 veces en lugar de una, gracias a un problema de N+1 queries introducido en el último deploy.",
        "academicas" => 3,
        "industriales" => 3
    ],
    [
        "id" => "H04",
        "titulo" => "Diseñar servicios sin estado (stateless)",
        "categoria" => "Arquitectura",
        "frecuencia" => 29.4,
        "fuentes" => 5,
        "consenso" => "Medio-alto",
        "descripcion" => "Diseñar cada servicio o instancia de servidor de modo que no almacene información de sesión o estado local del usuario entre solicitudes. Todo el estado necesario debe residir en un almacén externo compartido (base de datos, caché, token JWT).",
        "detalle" => "Si un servidor guarda la sesión del usuario en su memoria local, ese usuario siempre debe ser dirigido al mismo servidor. Esto crea afinidad de sesión (sticky sessions), que impide distribuir la carga libremente entre instancias. Si ese servidor falla, la sesión se pierde. Si necesitas agregar capacidad agregando más servidores, el balanceador de carga debe rastrear qué usuario va a cuál servidor, añadiendo complejidad. Un servicio sin estado elimina todos estos problemas: cualquier instancia puede atender cualquier solicitud porque no depende de memoria local. El estado del usuario vive en Redis o en un token autofirmado (JWT) que el cliente envía en cada petición. Esto hace que el escalado horizontal sea trivial: agregar más instancias es simplemente levantar más copias del mismo servicio.",
        "ejemplo" => 'En lugar de guardar la sesión en $_SESSION de PHP (que vive en el servidor local), se emite un JWT firmado al hacer login. Cada petición incluye ese token, el servidor lo verifica criptográficamente y extrae la identidad del usuario sin consultar ningún almacén de sesión.',
        "academicas" => 2,
        "industriales" => 3
    ],
    [
        "id" => "H05",
        "titulo" => "Dividir la aplicación en módulos débilmente acoplados",
        "categoria" => "Arquitectura",
        "frecuencia" => 29.4,
        "fuentes" => 5,
        "consenso" => "Medio-alto",
        "descripcion" => "Organizar el código y los servicios en unidades independientes con responsabilidades bien delimitadas, que se comunican entre sí a través de interfaces explícitas (APIs, eventos), minimizando las dependencias directas entre módulos.",
        "detalle" => "Un sistema monolítico donde todo está interconectado escala mal porque cualquier cambio puede afectar cualquier otra parte, los despliegues son de todo o nada, y un fallo en un componente puede tumbar el sistema completo. La modularidad resuelve esto estableciendo límites claros: cada módulo (o microservicio) tiene una responsabilidad única, expone una interfaz estable, y puede ser desplegado, escalado y actualizado de forma independiente. El bajo acoplamiento significa que si el módulo de pagos necesita escalar porque hay una promoción, puedes levantar más instancias de ese módulo sin tocar el resto del sistema. La alta cohesión significa que cada módulo agrupa lógica relacionada, haciendo el código más fácil de entender y mantener. Esta decisión debe tomarse desde el diseño inicial: separar módulos en un sistema ya construido es mucho más costoso que diseñarlos separados desde el principio.",
        "ejemplo" => "Un e-commerce divide su lógica en módulos de catálogo, carrito, pagos y envíos. Durante el Black Friday, solo el módulo de pagos necesita escalar x10. El resto del sistema permanece sin cambios. Si el servicio de envíos falla, los usuarios aún pueden navegar y comprar.",
        "academicas" => 2,
        "industriales" => 3
    ],
    [
        "id" => "H06",
        "titulo" => "Indexar correctamente las columnas consultadas con frecuencia",
        "categoria" => "Base de datos",
        "frecuencia" => 29.4,
        "fuentes" => 5,
        "consenso" => "Medio-alto",
        "descripcion" => "Crear índices de base de datos en las columnas que aparecen frecuentemente en cláusulas WHERE, JOIN, ORDER BY o GROUP BY, de modo que el motor de base de datos pueda localizar los registros relevantes sin escanear toda la tabla.",
        "detalle" => "Sin índices, una consulta SELECT * FROM orders WHERE user_id = 123 en una tabla con 10 millones de filas implica leer cada una de ellas para encontrar las que coinciden. Con un índice en user_id, el motor de base de datos puede localizar directamente las filas relevantes en tiempo logarítmico. Esto puede reducir el tiempo de una consulta de segundos a milisegundos. Sin embargo, los índices no son gratuitos: ocupan espacio en disco y ralentizan las operaciones de escritura (INSERT, UPDATE, DELETE) porque cada escritura debe actualizar también los índices correspondientes. La clave es indexar selectivamente: las columnas consultadas con frecuencia en lecturas, y evitar indexar columnas que se escriben mucho pero se leen poco. Herramientas como EXPLAIN en MySQL/PostgreSQL revelan si una consulta está usando índices o haciendo full table scans.",
        "ejemplo" => "Una aplicación de analytics consulta eventos por user_id y timestamp. Sin índice, la consulta tarda 4.2 segundos en una tabla de 50M registros. Agregando un índice compuesto (user_id, timestamp), la misma consulta tarda 8 milisegundos.",
        "academicas" => 3,
        "industriales" => 2
    ],
    [
        "id" => "H07",
        "titulo" => "Diseñar asumiendo que los componentes van a fallar",
        "categoria" => "Tolerancia a fallos",
        "frecuencia" => 23.5,
        "fuentes" => 4,
        "consenso" => "Medio",
        "descripcion" => "Construir el sistema desde el principio bajo la premisa de que cualquier componente —servidores, bases de datos, servicios externos, conexiones de red— puede fallar en cualquier momento. El diseño debe garantizar que el sistema continúe funcionando (aunque sea de forma degradada) cuando ocurran esos fallos.",
        "detalle" => "En sistemas distribuidos, el fallo parcial no es la excepción sino la norma. AWS, Google y Netflix publican regularmente post-mortems de incidentes en los que componentes individuales fallaron. La diferencia entre un sistema bien diseñado y uno mal diseñado no es si falla, sino cómo falla. Un sistema frágil falla completamente cuando un componente falla. Un sistema resiliente falla de forma degradada: si el servicio de recomendaciones está caído, el usuario aún puede ver el catálogo, aunque sin recomendaciones personalizadas. Las técnicas concretas incluyen: circuit breakers (dejar de llamar a un servicio que está fallando para no colapsar la cadena), retries con backoff exponencial (reintentar con esperas crecientes), bulkheads (aislar recursos para que un fallo no consuma todos los threads), y fallbacks (respuestas por defecto cuando un servicio no responde).",
        "ejemplo" => "Netflix implementa chaos engineering (Chaos Monkey) apagando instancias aleatoriamente en producción durante horas de trabajo. Esto obliga a los equipos a diseñar servicios que sobrevivan fallos, porque saben que ocurrirán.",
        "academicas" => 2,
        "industriales" => 2
    ],
    [
        "id" => "H08",
        "titulo" => "Usar balanceo de carga para distribuir tráfico",
        "categoria" => "Balanceo de carga",
        "frecuencia" => 23.5,
        "fuentes" => 4,
        "consenso" => "Medio",
        "descripcion" => "Colocar un balanceador de carga frente a las instancias del servidor que distribuya las solicitudes entrantes entre ellas según algún algoritmo (round-robin, least connections, IP hash), evitando que una sola instancia reciba más tráfico del que puede manejar.",
        "detalle" => "Un único servidor tiene un límite físico de solicitudes concurrentes que puede manejar. El balanceo de carga permite superar ese límite distribuyendo el trabajo entre múltiples instancias. Además de distribuir carga, el balanceador actúa como punto de control: puede detectar instancias que no responden y dejar de enviarles tráfico (health checks), facilitar deploys sin downtime dirigiendo tráfico solo a instancias ya actualizadas (rolling deployments), y terminar conexiones SSL centralizadamente. Existen balanceadores a nivel de red (L4, como AWS NLB) que operan sobre TCP/UDP, y balanceadores a nivel de aplicación (L7, como AWS ALB o nginx) que pueden enrutar basándose en rutas URL, headers o contenido del cuerpo. La elección depende de la complejidad del enrutamiento requerido.",
        "ejemplo" => "Tres instancias del servidor web atienden tráfico. El balanceador detecta que la instancia 2 no responde a los health checks y deja de enviarle tráfico en menos de 30 segundos, sin que los usuarios noten interrupción del servicio.",
        "academicas" => 1,
        "industriales" => 3
    ],
    [
        "id" => "H09",
        "titulo" => "Usar replicación de base de datos para separar lecturas de escrituras",
        "categoria" => "Base de datos",
        "frecuencia" => 23.5,
        "fuentes" => 4,
        "consenso" => "Medio",
        "descripcion" => "Mantener un nodo primario de base de datos que recibe todas las escrituras, y uno o más nodos réplica que replican los datos del primario y atienden las consultas de lectura, distribuyendo así la carga entre múltiples servidores.",
        "detalle" => "En la mayoría de las aplicaciones web, el 80-90% de las operaciones son lecturas. Si todas esas lecturas y todas las escrituras van al mismo servidor, ese servidor se convierte rápidamente en un cuello de botella. La replicación separa estas cargas: el nodo primario se enfoca en escrituras (que requieren consistencia y transacciones), mientras que varios nodos réplica sirven lecturas de forma paralela. El resultado es que la capacidad de lectura escala horizontalmente agregando más réplicas, sin aumentar la carga sobre el primario. La consideración principal es la replicación asíncrona: existe un pequeño retraso (lag de replicación) entre cuando algo se escribe en el primario y cuando aparece en las réplicas. Para la mayoría de los casos de uso esto es aceptable, pero operaciones que requieren leer inmediatamente lo que acaban de escribir deben dirigirse al primario.",
        "ejemplo" => "Una plataforma de noticias tiene un nodo primario para que los editores publiquen artículos, y tres réplicas de lectura que sirven las páginas públicas. Un pico de tráfico por una noticia viral solo afecta a las réplicas; el sistema de publicación permanece estable.",
        "academicas" => 2,
        "industriales" => 2
    ],
    [
        "id" => "H10",
        "titulo" => "Preferir escalado horizontal sobre vertical",
        "categoria" => "Escalado horizontal/vertical",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Diseñar el sistema para crecer agregando más instancias (servidores, contenedores) en lugar de hacer el servidor existente más potente. El escalado horizontal permite crecimiento prácticamente ilimitado y mayor tolerancia a fallos.",
        "detalle" => "El escalado vertical (scale-up) tiene un límite físico: hay un servidor máximo que puedes comprar, y cada salto de capacidad es un evento de downtime o un costo desproporcionado. El escalado horizontal (scale-out) no tiene ese límite: si necesitas más capacidad, agregas más instancias iguales. Además, un único servidor grande representa un punto único de falla; diez servidores pequeños pueden perder uno sin interrumpir el servicio. El prerequisito para escalar horizontalmente es que el sistema sea stateless (H04) y que los datos compartidos estén externalizados. No siempre es la respuesta correcta para todo: una base de datos transaccional es más difícil de escalar horizontalmente que un servidor web. Por eso esta heurística aplica principalmente a la capa de aplicación, donde es más natural y menos costosa de implementar.",
        "ejemplo" => "Durante el Mundial de Fútbol, una aplicación de estadísticas pasa de 5 a 50 instancias en 10 minutos usando autoescalado. Al terminar el evento, vuelve a 5. Con escalado vertical, ese pico habría requerido migrar a un servidor 10x más potente, con horas de downtime.",
        "academicas" => 2,
        "industriales" => 1
    ],
    [
        "id" => "H11",
        "titulo" => "Procesar tareas pesadas de forma asíncrona",
        "categoria" => "Escalado horizontal/vertical",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Mover las operaciones que consumen tiempo (envío de emails, generación de reportes, procesamiento de imágenes, llamadas a APIs externas lentas) fuera del ciclo de request-response, colocándolas en una cola que las ejecuta en background.",
        "detalle" => "Cuando un usuario hace una solicitud HTTP, mantenerlo esperando 30 segundos mientras se procesa un video es inaceptable y bloquea un thread del servidor durante todo ese tiempo. Las colas de mensajes (RabbitMQ, AWS SQS, Redis Queue) separan la aceptación del trabajo de su ejecución. El servidor acepta la tarea, la encola, y responde inmediatamente al usuario (202 Accepted). Workers independientes toman tareas de la cola y las procesan en segundo plano. Esto tiene múltiples beneficios: el usuario recibe respuesta inmediata, los threads del servidor web se liberan para atender otras solicitudes, los workers pueden escalarse independientemente según el volumen de la cola, y si un worker falla, la tarea vuelve a la cola para ser procesada por otro. También permite absorber picos de tráfico: si llegan mil solicitudes simultáneas, la cola las almacena y los workers las procesan a su ritmo.",
        "ejemplo" => "Al registrarse, un usuario recibe un email de bienvenida. En lugar de llamar al servidor SMTP durante el request (300ms adicionales), se encola el envío. El usuario ve la confirmación de registro en 50ms; el email llega segundos después.",
        "academicas" => 1,
        "industriales" => 2
    ],
    [
        "id" => "H12",
        "titulo" => "Usar computación serverless para escalar automáticamente",
        "categoria" => "Escalado horizontal/vertical",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Implementar componentes de la aplicación como funciones serverless (AWS Lambda, Google Cloud Functions, Vercel Functions) que se ejecutan bajo demanda, escalan automáticamente de cero a miles de instancias según el tráfico, y solo generan costo cuando se ejecutan.",
        "detalle" => "El modelo serverless transfiere la responsabilidad del escalado de infraestructura al proveedor cloud. En lugar de mantener servidores corriendo 24/7 esperando solicitudes, cada invocación de la función levanta su propio entorno de ejecución, procesa la solicitud y termina. El escalado es automático e instantáneo: si llegan mil solicitudes simultáneas, se ejecutan mil instancias de la función en paralelo. Esto es especialmente útil para cargas de trabajo variables o impredecibles, APIs de baja frecuencia donde mantener un servidor encendido no es rentable, y procesamiento de eventos en background. Las limitaciones incluyen la latencia de cold start (la primera invocación puede ser más lenta), límites de tiempo de ejecución, y mayor costo por invocación comparado con instancias dedicadas cuando el tráfico es muy alto y constante.",
        "ejemplo" => "Una startup procesa imágenes subidas por usuarios con una función Lambda. Con 10 usuarios al día, el costo es prácticamente cero. Cuando una campaña viral lleva el tráfico a 100,000 subidas en una hora, la función escala automáticamente sin intervención manual.",
        "academicas" => 1,
        "industriales" => 2
    ],
    [
        "id" => "H13",
        "titulo" => "Monitorear continuamente métricas clave del sistema",
        "categoria" => "Monitoreo y observabilidad",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Recolectar y visualizar en tiempo real las métricas fundamentales del sistema —latencia de respuesta, tasa de errores, uso de CPU y memoria, throughput— con alertas automáticas que notifiquen cuando se superan umbrales críticos.",
        "detalle" => "No se puede escalar lo que no se mide. El monitoreo continuo permite detectar problemas antes de que los usuarios los reporten, identificar tendencias de crecimiento para planificar capacidad, y confirmar que los cambios de arquitectura realmente mejoran el rendimiento. Las métricas fundamentales se agrupan en los cuatro señales doradas de Google SRE: latencia (cuánto tarda el sistema en responder), tráfico (cuántas solicitudes por segundo procesa), errores (qué porcentaje de solicitudes falla) y saturación (cuán lleno está el sistema, en CPU, memoria o disco). Herramientas como Prometheus, Grafana, Datadog o AWS CloudWatch permiten recolectar estas métricas, visualizarlas en dashboards y configurar alertas. Sin monitoreo, se opera a ciegas: los problemas se descubren cuando los usuarios se quejan, no cuando comienzan.",
        "ejemplo" => "Una alerta se dispara cuando la latencia del percentil 95 supera 500ms. El equipo investiga y descubre que una consulta sin índice fue introducida en el último deploy. Se revierte el cambio antes de que el 99% de los usuarios lo noten.",
        "academicas" => 1,
        "industriales" => 2
    ],
    [
        "id" => "H14",
        "titulo" => "Elegir el tipo de base de datos adecuado según el caso de uso",
        "categoria" => "Base de datos",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Seleccionar el motor de base de datos cuyo modelo de datos y garantías de consistencia se alineen con los patrones de acceso del sistema, en lugar de usar siempre una base de datos relacional por defecto.",
        "detalle" => "No existe una base de datos universalmente óptima. Las relacionales (PostgreSQL, MySQL) ofrecen transacciones ACID, joins complejos y esquemas rígidos, ideales cuando la consistencia de datos es crítica (pagos, inventario). Las de documentos (MongoDB) permiten esquemas flexibles y datos anidados, útiles cuando la estructura de los datos varía mucho entre registros. Las de clave-valor (Redis) son extremadamente rápidas para acceso por clave, perfectas para caché y sesiones. Las columnares (Cassandra) escalan horizontalmente de forma nativa y están optimizadas para escrituras masivas y consultas por rangos de tiempo. Las de grafos (Neo4j) modelan relaciones complejas entre entidades de forma natural. Usar PostgreSQL para todo porque es lo conocido puede funcionar al principio, pero llegar a millones de registros con el modelo de datos equivocado puede requerir una migración completa.",
        "ejemplo" => "Un sistema de analítica de eventos usa Cassandra para almacenar millones de eventos por día (optimizado para escrituras) y Redis para los contadores en tiempo real que se muestran en el dashboard (optimizado para lecturas rápidas), mientras que los datos de usuarios siguen en PostgreSQL.",
        "academicas" => 2,
        "industriales" => 1
    ],
    [
        "id" => "H15",
        "titulo" => "Adoptar arquitectura de microservicios sobre monolitos",
        "categoria" => "Arquitectura",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Dividir la aplicación en servicios pequeños e independientes, cada uno con su propia base de datos y responsabilidad de negocio bien definida, desplegados y escalados de forma autónoma.",
        "detalle" => "Los microservicios permiten escalar componentes individuales según sus necesidades específicas, en lugar de escalar todo el sistema. Un servicio de búsqueda con alta demanda puede tener 20 instancias mientras el servicio de reportes tiene 2. Cada equipo puede desarrollar, desplegar y escalar su servicio de forma independiente sin coordinar con otros equipos. Sin embargo, los microservicios introducen complejidad operativa significativa: comunicación en red entre servicios (con su latencia y posibilidad de fallo), gestión de múltiples bases de datos, trazabilidad distribuida, y mayor overhead de infraestructura. La literatura coincide en una advertencia importante: empezar con microservicios en un proyecto nuevo sin conocer aún los límites naturales del dominio suele resultar en una arquitectura mal dividida que es más difícil de mantener que un monolito bien estructurado. La recomendación es empezar con un monolito modular y extraer servicios cuando los cuellos de botella sean evidentes.",
        "ejemplo" => "Amazon comenzó como un monolito en la década de 1990. Migró a microservicios durante los 2000s cuando el crecimiento hizo imposible que cientos de equipos trabajaran en el mismo codebase sin bloquearse mutuamente.",
        "academicas" => 1,
        "industriales" => 2
    ],
    [
        "id" => "H16",
        "titulo" => "Usar contenedores para consistencia de despliegue",
        "categoria" => "CI/CD y despliegue",
        "frecuencia" => 17.6,
        "fuentes" => 3,
        "consenso" => "Medio",
        "descripcion" => "Empaquetar la aplicación y todas sus dependencias en contenedores (Docker) que garantizan que el mismo artefacto se comporta de forma idéntica en desarrollo, pruebas y producción, y que puede desplegarse y escalarse en segundos.",
        "detalle" => "Los contenedores eliminan la clase de problemas 'en mi máquina funciona'. Al empaquetar el código junto con su runtime, dependencias del sistema operativo y configuración en una imagen inmutable, se garantiza que lo que se prueba es exactamente lo que se despliega. Para la escalabilidad, los contenedores son fundamentales porque hacen que cada instancia del servicio sea idéntica e intercambiable: el orquestador (Kubernetes, ECS) puede levantar nuevas instancias en segundos copiando la imagen, sin tiempos de instalación o configuración. También facilitan el escalado horizontal porque cada contenedor es autocontenido y no requiere configuración adicional al levantarse. La inmutabilidad de las imágenes también mejora la seguridad y la auditabilidad: siempre se sabe exactamente qué está corriendo en producción.",
        "ejemplo" => "Un equipo levanta un entorno de 10 instancias del mismo servicio en 45 segundos usando Docker y Kubernetes. Cada instancia es idéntica. Al detectar un bug, hacer rollback a la versión anterior toma 30 segundos: solo cambia la etiqueta de la imagen.",
        "academicas" => 2,
        "industriales" => 1
    ]
];

$heuristicas_secundarias = [
    [
        "id" => "S01",
        "titulo" => "Implementar autoescalado según demanda",
        "categoria" => "Escalado horizontal/vertical",
        "frecuencia" => 11.8,
        "descripcion" => "Configurar reglas que aumenten o disminuyan automáticamente el número de instancias en ejecución según métricas de carga en tiempo real, sin intervención manual.",
        "detalle" => "El autoescalado combina el monitoreo continuo (H13) con el escalado horizontal (H10) de forma automática. Se definen umbrales: si el CPU supera el 70% por más de 5 minutos, agregar 2 instancias; si baja del 30% por 10 minutos, eliminar 1. Esto optimiza costos (no se paga por capacidad ociosa) y garantiza disponibilidad ante picos imprevistos."
    ],
    [
        "id" => "S02",
        "titulo" => "Aplicar sharding de base de datos",
        "categoria" => "Base de datos",
        "frecuencia" => 11.8,
        "descripcion" => "Particionar horizontalmente los datos de una base de datos en múltiples servidores independientes, donde cada servidor contiene un subconjunto de los datos según alguna clave de partición.",
        "detalle" => "Cuando una base de datos supera la capacidad de un solo servidor, el sharding distribuye los datos entre varios. Por ejemplo, usuarios con ID 1-1M en el shard 1, ID 1M-2M en el shard 2. Esto distribuye tanto el almacenamiento como la carga de escritura. La complejidad está en las consultas que necesitan datos de múltiples shards, que deben agregarse en la aplicación."
    ],
    [
        "id" => "S03",
        "titulo" => "Adoptar un enfoque API-first en el diseño",
        "categoria" => "Diseño de APIs",
        "frecuencia" => 11.8,
        "descripcion" => "Diseñar y documentar la API antes de implementar la lógica de negocio, tratando la interfaz como el contrato principal del servicio.",
        "detalle" => "API-first garantiza que los servicios exponen interfaces bien definidas desde el inicio, facilitando el desacoplamiento entre equipos y servicios. La documentación (OpenAPI/Swagger) se convierte en la fuente de verdad. Esto reduce dependencias implícitas y permite que múltiples equipos desarrollen en paralelo contra la misma especificación."
    ],
    [
        "id" => "S04",
        "titulo" => "Usar un API gateway como punto de entrada único",
        "categoria" => "Diseño de APIs",
        "frecuencia" => 11.8,
        "descripcion" => "Centralizar todas las solicitudes entrantes en un único punto que gestiona autenticación, autorización, rate limiting, logging y enrutamiento hacia los servicios internos.",
        "detalle" => "El API gateway evita que cada microservicio tenga que implementar autenticación, rate limiting y logging de forma independiente. Centraliza estas preocupaciones transversales, reduce la superficie de ataque al no exponer servicios internos directamente, y permite cambios en el enrutamiento sin modificar los clientes."
    ],
    [
        "id" => "S05",
        "titulo" => "Aplicar rate limiting en las APIs",
        "categoria" => "Diseño de APIs",
        "frecuencia" => 11.8,
        "descripcion" => "Limitar el número de solicitudes que un cliente o usuario puede hacer en un período de tiempo determinado, protegiendo el sistema de abuso, ataques de denegación de servicio y clientes defectuosos.",
        "detalle" => "Sin rate limiting, un solo cliente mal configurado puede hacer miles de solicitudes por segundo, saturando los recursos del sistema y degradando la experiencia de todos los usuarios. El rate limiting puede aplicarse por IP, por API key, por usuario autenticado, o por ruta específica. Los clientes legítimos raramente superan los límites razonables."
    ],
    [
        "id" => "S06",
        "titulo" => "Usar replicación multi-zona de disponibilidad",
        "categoria" => "Tolerancia a fallos",
        "frecuencia" => 11.8,
        "descripcion" => "Distribuir las instancias del servicio en múltiples zonas de disponibilidad o regiones geográficas para garantizar que un fallo de infraestructura en una zona no interrumpa el servicio completo.",
        "detalle" => "Los proveedores cloud dividen su infraestructura en zonas de disponibilidad (datacenters independientes con energía, red y refrigeración separadas). Desplegar en múltiples zonas garantiza que una falla eléctrica o de red en una zona no afecte a las instancias en otras. Es el fundamento de la alta disponibilidad en entornos cloud."
    ],
    [
        "id" => "S07",
        "titulo" => "Aplicar circuit breakers para evitar fallos en cascada",
        "categoria" => "Tolerancia a fallos",
        "frecuencia" => 11.8,
        "descripcion" => "Implementar el patrón circuit breaker que detecta cuando un servicio dependiente está fallando y deja de llamarlo temporalmente, devolviendo una respuesta de fallback en lugar de esperar timeouts indefinidamente.",
        "detalle" => "Sin circuit breakers, si el servicio A llama al servicio B que está caído, A espera el timeout (varios segundos) en cada solicitud. Esto agota los threads de A, que comienza a fallar también, propagando el fallo en cascada. El circuit breaker detecta la tasa de errores y abre el circuito: en lugar de llamar a B, devuelve inmediatamente una respuesta por defecto. Periódicamente prueba si B se recuperó para cerrar el circuito."
    ],
    [
        "id" => "S08",
        "titulo" => "Diseñar operaciones idempotentes",
        "categoria" => "Tolerancia a fallos",
        "frecuencia" => 5.9,
        "descripcion" => "Diseñar operaciones de modo que ejecutarlas múltiples veces con los mismos parámetros produzca el mismo resultado que ejecutarlas una sola vez, permitiendo reintentos seguros ante fallos de red.",
        "detalle" => "En sistemas distribuidos, las redes fallan y los timeouts ocurren. Cuando un cliente no recibe respuesta, no sabe si la operación se ejecutó o no. Si la operación es idempotente, puede reintentarla con seguridad. Ejemplo: en lugar de POST /orders (crea una orden cada vez), usar PUT /orders/{idempotency-key} que crea la orden si no existe o devuelve la orden existente si ya fue procesada."
    ],
    [
        "id" => "S09",
        "titulo" => "Adoptar arquitectura multi-tier separando capas",
        "categoria" => "Arquitectura",
        "frecuencia" => 5.9,
        "descripcion" => "Separar la aplicación en capas físicamente distintas: presentación (servidor web), lógica de negocio (servidor de aplicación) y datos (base de datos), cada una escalable de forma independiente.",
        "detalle" => "La separación en tiers permite escalar cada capa según sus necesidades: más servidores web para manejar más conexiones HTTP, más servidores de aplicación para más procesamiento de lógica, y más servidores de base de datos para más datos. También mejora la seguridad al limitar qué capa tiene acceso a qué recursos."
    ],
    [
        "id" => "S10",
        "titulo" => "Usar connection pooling para la base de datos",
        "categoria" => "Base de datos",
        "frecuencia" => 5.9,
        "descripcion" => "Mantener un conjunto de conexiones abiertas a la base de datos que se reutilizan entre solicitudes, en lugar de abrir y cerrar una conexión nueva en cada request.",
        "detalle" => "Establecer una conexión a la base de datos tiene un costo no trivial: handshake TCP, autenticación, negociación de parámetros. Con miles de solicitudes por segundo, ese overhead se acumula. El connection pool mantiene N conexiones abiertas permanentemente y las presta a los requests que las necesitan. Cuando el request termina, devuelve la conexión al pool en lugar de cerrarla."
    ],
    [
        "id" => "S11",
        "titulo" => "Implementar CQRS para separar lecturas de escrituras",
        "categoria" => "Base de datos",
        "frecuencia" => 11.8,
        "descripcion" => "Usar modelos y rutas de datos distintos para las operaciones de lectura (queries) y escritura (commands), optimizando cada uno para sus patrones de acceso específicos.",
        "detalle" => "CQRS (Command Query Responsibility Segregation) reconoce que leer y escribir datos tienen requisitos muy distintos. Las escrituras necesitan consistencia y validación; las lecturas necesitan velocidad y flexibilidad de presentación. Separando los modelos, las lecturas pueden usar vistas desnormalizadas optimizadas para consultas específicas, mientras las escrituras usan el modelo normalizado correcto."
    ],
    [
        "id" => "S12",
        "titulo" => "Implementar CI/CD para despliegues frecuentes",
        "categoria" => "CI/CD y despliegue",
        "frecuencia" => 5.9,
        "descripcion" => "Automatizar el pipeline de integración, pruebas y despliegue de modo que cada cambio de código se valide automáticamente y pueda llegar a producción en minutos con mínimo riesgo.",
        "detalle" => "CI/CD no es solo comodidad: es una práctica de escalabilidad organizacional. Cuando los deploys son frecuentes y pequeños, cada cambio tiene un impacto limitado y los errores son fáciles de identificar y revertir. Los deploys grandes y espaciados acumulan riesgos y hacen que los rollbacks sean traumáticos."
    ],
    [
        "id" => "S13",
        "titulo" => "Aplicar lazy loading de recursos no esenciales",
        "categoria" => "Frontend/cliente",
        "frecuencia" => 11.8,
        "descripcion" => "Retrasar la carga de recursos (imágenes, scripts, componentes) que no son necesarios para el renderizado inicial de la página, descargándolos solo cuando el usuario los necesita.",
        "detalle" => "El tiempo de carga inicial de una página determina si el usuario se queda o abandona. Cargar todo desde el principio (imágenes fuera del viewport, componentes de funciones que el usuario quizás nunca use) penaliza ese tiempo de forma innecesaria. Lazy loading prioriza lo visible y difiere el resto."
    ],
    [
        "id" => "S14",
        "titulo" => "Usar orquestación de contenedores para autoescalado",
        "categoria" => "Escalado horizontal/vertical",
        "frecuencia" => 11.8,
        "descripcion" => "Usar plataformas como Kubernetes o Amazon ECS para gestionar automáticamente el ciclo de vida de los contenedores, incluyendo escalado, autorrecuperación ante fallos y distribución de carga.",
        "detalle" => "La orquestación de contenedores automatiza lo que sería imposible hacer manualmente a escala: detectar que una instancia falló y levantar otra en segundos, distribuir instancias entre nodos según recursos disponibles, escalar según métricas de carga, y gestionar actualizaciones sin downtime. Kubernetes se ha convertido en el estándar de facto para esto."
    ],
    [
        "id" => "S15",
        "titulo" => "Basar decisiones de escalado en métricas reales",
        "categoria" => "Monitoreo y observabilidad",
        "frecuencia" => 5.9,
        "descripcion" => "Tomar decisiones de arquitectura y escalado basadas en datos medidos del comportamiento real del sistema, no en suposiciones o tendencias tecnológicas.",
        "detalle" => "La optimización prematura es costosa en tiempo y complejidad. Adoptar microservicios, sharding, o caching sin evidencia de que son necesarios introduce complejidad sin beneficio. El enfoque correcto es medir primero, identificar el cuello de botella real con datos, y luego aplicar la solución específica para ese problema."
    ],
    [
        "id" => "S16",
        "titulo" => "Usar mensajería basada en eventos entre servicios",
        "categoria" => "Arquitectura",
        "frecuencia" => 5.9,
        "descripcion" => "Comunicar servicios mediante la publicación y consumo de eventos a través de un broker de mensajes (Kafka, RabbitMQ), en lugar de llamadas sincrónicas directas entre servicios.",
        "detalle" => "La comunicación sincrónica crea acoplamiento temporal: el servicio A no puede completar su trabajo sin que el servicio B responda. Si B está lento o caído, A sufre. La mensajería por eventos desacopla esta dependencia: A publica un evento 'orden creada' y continúa; B, C y D consumen ese evento cuando pueden. Esto mejora la resiliencia y permite que cada servicio escale independientemente."
    ],
    [
        "id" => "S17",
        "titulo" => "Aplicar chaos engineering para validar resiliencia",
        "categoria" => "Tolerancia a fallos",
        "frecuencia" => 11.8,
        "descripcion" => "Inyectar fallos deliberados en el sistema en condiciones controladas para descubrir debilidades antes de que ocurran en producción de forma no planificada.",
        "detalle" => "El chaos engineering, popularizado por Netflix con Chaos Monkey, parte de la premisa de que la única forma de saber cómo falla un sistema es hacerlo fallar de forma controlada. Se apagan instancias aleatoriamente, se introduce latencia artificial, se cortan conexiones de red. Los problemas descubiertos así tienen solución planificada; los descubiertos en un incidente real tienen presión de tiempo."
    ],
    [
        "id" => "S18",
        "titulo" => "Evitar over-fetching de datos en consultas",
        "categoria" => "Base de datos",
        "frecuencia" => 5.9,
        "descripcion" => "Diseñar las consultas para recuperar únicamente los datos que se necesitan, evitando SELECT * o traer miles de registros cuando solo se necesita saber si existe uno.",
        "detalle" => "Cada byte transferido entre la base de datos y la aplicación consume CPU, memoria y ancho de banda de red. SELECT * en una tabla con 50 columnas cuando solo se necesitan 3 es un desperdicio multiplicado por cada solicitud. Usar EXISTS en lugar de COUNT(*) para verificar existencia, LIMIT para paginar resultados, y seleccionar solo las columnas necesarias son prácticas que reducen la carga de forma proporcional al volumen."
    ],
    [
        "id" => "S19",
        "titulo" => "Implementar paginación en lugar de devolver todos los registros",
        "categoria" => "Diseño de APIs",
        "frecuencia" => 5.9,
        "descripcion" => "Diseñar las APIs para devolver los datos en páginas de tamaño limitado, con mecanismos para navegar entre páginas, en lugar de devolver todos los registros en una sola respuesta.",
        "detalle" => "Una API que devuelve todos los registros de una tabla sin límite es una bomba de tiempo. Con 100 registros funciona bien. Con 100,000 registros, el servidor consume memoria serializing todos los registros, la red transfiere megabytes, y el cliente se cuelga procesando la respuesta. La paginación por offset (LIMIT/OFFSET) o por cursor (WHERE id > last_seen_id) mantiene las respuestas acotadas independientemente del volumen total."
    ],
    [
        "id" => "S20",
        "titulo" => "Definir SLA, SLO y SLI para el sistema",
        "categoria" => "Monitoreo y observabilidad",
        "frecuencia" => 5.9,
        "descripcion" => "Establecer formalmente los acuerdos de nivel de servicio (SLA), objetivos de nivel de servicio (SLO) y los indicadores que los miden (SLI) para tener criterios objetivos de cuándo el sistema está funcionando bien o mal.",
        "detalle" => "Sin definiciones formales de 'qué es aceptable', es imposible saber cuándo hay un problema que merece atención vs. variación normal. Un SLO como 'el P99 de latencia debe ser menor a 500ms' convierte el monitoreo en algo accionable. El SLI es la métrica medida (latencia P99 real). El SLA es el compromiso con el cliente sobre ese objetivo. Juntos crean una cultura de confiabilidad basada en datos."
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heurísticas de Escalabilidad — Aplicaciones Web</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span class="logo-mark">⬡</span>
                <span class="logo-text">Escalabilidad Web</span>
            </div>
            <p class="sidebar-sub">Catálogo de heurísticas sistematizadas</p>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <button class="nav-section-toggle active" data-target="nav-principal">
                    <span>Heurísticas Principales</span>
                    <span class="nav-count"><?= count($heuristicas_principales) ?></span>
                </button>
                <ul class="nav-list" id="nav-principal">
                    <?php foreach ($heuristicas_principales as $h): ?>
                    <li>
                        <a href="#" class="nav-item" data-id="<?= $h['id'] ?>" data-group="principal">
                            <span class="nav-item-id"><?= $h['id'] ?></span>
                            <span class="nav-item-title"><?= $h['titulo'] ?></span>
                            <span class="nav-item-cat cat-<?= strtolower(str_replace([' ', '/'], '-', $h['categoria'])) ?>"><?= $h['categoria'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="nav-section">
                <button class="nav-section-toggle" data-target="nav-secundario">
                    <span>Heurísticas Secundarias</span>
                    <span class="nav-count"><?= count($heuristicas_secundarias) ?></span>
                </button>
                <ul class="nav-list collapsed" id="nav-secundario">
                    <?php foreach ($heuristicas_secundarias as $h): ?>
                    <li>
                        <a href="#" class="nav-item" data-id="<?= $h['id'] ?>" data-group="secundario">
                            <span class="nav-item-id"><?= $h['id'] ?></span>
                            <span class="nav-item-title"><?= $h['titulo'] ?></span>
                            <span class="nav-item-cat cat-<?= strtolower(str_replace([' ', '/'], '-', $h['categoria'])) ?>"><?= $h['categoria'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <p>Corpus: 17 fuentes · 145 heurísticas brutas</p>
            <p>Umbral de consenso: ≥ 15%</p>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content" id="main-content">

        <!-- HOME SCREEN -->
        <div class="view active" id="view-home">
            <div class="home-hero">
                <div class="home-hero-eyebrow">Investigación · Universidad Tecnológica de Panamá</div>
                <h1 class="home-title">Heurísticas de Escalabilidad<br>para Aplicaciones Web</h1>
                <p class="home-desc">Un catálogo sistematizado de <?= count($heuristicas_principales) + count($heuristicas_secundarias) ?> principios de diseño derivados del análisis de frecuencia sobre <?= 17 ?> fuentes académicas e industriales. Las heurísticas están ordenadas por grado de consenso entre la literatura revisada.</p>
                <div class="home-stats">
                    <div class="stat">
                        <span class="stat-num"><?= count($heuristicas_principales) ?></span>
                        <span class="stat-label">Heurísticas principales<br><small>consenso ≥ 15%</small></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat">
                        <span class="stat-num"><?= count($heuristicas_secundarias) ?></span>
                        <span class="stat-label">Heurísticas secundarias<br><small>consenso &lt; 15%</small></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat">
                        <span class="stat-num">8</span>
                        <span class="stat-label">Categorías de<br><small>diseño</small></span>
                    </div>
                </div>
                <p class="home-cta-hint">Selecciona una heurística en el panel lateral para comenzar.</p>
            </div>

            <div class="home-categories">
                <h2 class="section-title">Categorías</h2>
                <div class="cat-grid">
                    <?php
                    $cats = [];
                    foreach (array_merge($heuristicas_principales, $heuristicas_secundarias) as $h) {
                        $cats[$h['categoria']] = ($cats[$h['categoria']] ?? 0) + 1;
                    }
                    arsort($cats);
                    foreach ($cats as $cat => $count):
                        $slug = strtolower(str_replace([' ', '/'], '-', $cat));
                    ?>
                    <div class="cat-card cat-card-<?= $slug ?>">
                        <span class="cat-card-name"><?= $cat ?></span>
                        <span class="cat-card-count"><?= $count ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- HEURISTIC DETAIL VIEW (principal) -->
        <?php foreach ($heuristicas_principales as $h): ?>
        <div class="view" id="view-<?= $h['id'] ?>">
            <div class="detail-header">
                <div class="detail-meta">
                    <span class="detail-id"><?= $h['id'] ?></span>
                    <span class="detail-cat cat-<?= strtolower(str_replace([' ', '/'], '-', $h['categoria'])) ?>"><?= $h['categoria'] ?></span>
                    <span class="detail-badge">Principal</span>
                </div>
                <h1 class="detail-title"><?= $h['titulo'] ?></h1>
                <p class="detail-summary"><?= $h['descripcion'] ?></p>

                <div class="consensus-bar-wrap">
                    <div class="consensus-bar-label">
                        <span>Índice de consenso</span>
                        <span class="consensus-val"><?= $h['frecuencia'] ?>%</span>
                    </div>
                    <div class="consensus-track">
                        <div class="consensus-fill" style="width: <?= $h['frecuencia'] ?>%"></div>
                    </div>
                    <div class="consensus-sources">
                        Mencionada en <strong><?= $h['fuentes'] ?></strong> de 17 fuentes ·
                        <span class="src-acad"><?= $h['academicas'] ?> académicas</span> ·
                        <span class="src-ind"><?= $h['industriales'] ?> industriales</span>
                    </div>
                </div>
            </div>

            <div class="detail-body">
                <section class="detail-section">
                    <h2>Explicación detallada</h2>
                    <p><?= $h['detalle'] ?></p>
                </section>

                <section class="detail-section">
                    <h2>Ejemplo práctico</h2>
                    <div class="example-block">
                        <p><?= $h['ejemplo'] ?></p>
                    </div>
                </section>
            </div>

            <div class="detail-nav">
                <?php
                $idx = array_search($h, $heuristicas_principales);
                $prev = $heuristicas_principales[$idx - 1] ?? null;
                $next = $heuristicas_principales[$idx + 1] ?? null;
                ?>
                <?php if ($prev): ?>
                <button class="nav-btn nav-btn-prev" data-id="<?= $prev['id'] ?>" data-group="principal">
                    ← <?= $prev['id'] ?>: <?= $prev['titulo'] ?>
                </button>
                <?php endif; ?>
                <?php if ($next): ?>
                <button class="nav-btn nav-btn-next" data-id="<?= $next['id'] ?>" data-group="principal">
                    <?= $next['id'] ?>: <?= $next['titulo'] ?> →
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- HEURISTIC DETAIL VIEW (secundario) -->
        <?php foreach ($heuristicas_secundarias as $h): ?>
        <div class="view" id="view-<?= $h['id'] ?>">
            <div class="detail-header">
                <div class="detail-meta">
                    <span class="detail-id"><?= $h['id'] ?></span>
                    <span class="detail-cat cat-<?= strtolower(str_replace([' ', '/'], '-', $h['categoria'])) ?>"><?= $h['categoria'] ?></span>
                    <span class="detail-badge detail-badge-sec">Secundaria</span>
                </div>
                <h1 class="detail-title"><?= $h['titulo'] ?></h1>
                <p class="detail-summary"><?= $h['descripcion'] ?></p>

                <div class="consensus-bar-wrap">
                    <div class="consensus-bar-label">
                        <span>Índice de consenso</span>
                        <span class="consensus-val"><?= $h['frecuencia'] ?>%</span>
                    </div>
                    <div class="consensus-track">
                        <div class="consensus-fill consensus-fill-sec" style="width: <?= $h['frecuencia'] ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="detail-body">
                <section class="detail-section">
                    <h2>Explicación</h2>
                    <p><?= $h['detalle'] ?></p>
                </section>
            </div>

            <div class="detail-nav">
                <?php
                $idx = array_search($h, $heuristicas_secundarias);
                $prev = $heuristicas_secundarias[$idx - 1] ?? null;
                $next = $heuristicas_secundarias[$idx + 1] ?? null;
                ?>
                <?php if ($prev): ?>
                <button class="nav-btn nav-btn-prev" data-id="<?= $prev['id'] ?>" data-group="secundario">
                    ← <?= $prev['id'] ?>: <?= $prev['titulo'] ?>
                </button>
                <?php endif; ?>
                <?php if ($next): ?>
                <button class="nav-btn nav-btn-next" data-id="<?= $next['id'] ?>" data-group="secundario">
                    <?= $next['id'] ?>: <?= $next['titulo'] ?> →
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </main>
</div>

<!-- Mobile toggle -->
<button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Abrir menú">☰</button>

<script src="function.js"></script>
</body>
</html>