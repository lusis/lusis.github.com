<html>
<body>
<i>This is just a few check commands I use to monitor various devices:</i>
<?php echo "<br/>This file was modified on: ". date("F d Y H:i:s T", filemtime(__FILE__)) ?>
<h1>Name</h1>
	<h3>check_ns_vpn_status</h3>

<h2>Purpose</h2>
	Check the status of a VPN tunnel on a Netscreen Device
<br/>
<h3>checkcommands.cfg stanza:</h3>
<pre>
	define command {
	       command_name       check_nsvpn_status
	       command_line       $USER1$/check_snmp -H $HOSTNAME$ -C $ARG1$ -o nsVpnMonP2State.$ARG2$ -l $ARG3$ -c 1:
	}
</pre>
<h2>Notes</h2>
	This expects you to have the ns51 mib files from Juniper registered with your SNMP subsystem. $ARG2$ is the id of the tunnel that you wish to monitor.<br/>
	You can get the ID from an snmpwalk:
<pre>
	export MIBS=ALL
	snmpwalk -v 1 -Cc -Os -c myrocommunity 1.1.1.1 netscreenVpnMonVpnName
</pre>
	Which will return something like this:
<pre>
	nsVpnMonVpnName.0 = STRING: "IKE-CompanyA2CompanyB"
	nsVpnMonVpnName.1 = STRING: "IKE-CompanyA2CompanyC"
</pre>
	From this we know that "IKE-CompanyA2CompanyB" is index 0 and "IKE-CompanyA2CompanyC" is index 1. If you want to walk the whole netscreenVpnMon just use that instead of netscreenVpnMonVpnName. There are several stats there that you might be interested in.
<br/>
	In our case, we want to validate that the tunnel is up. We may have endpoints on the other end of this B2B tunnel that we don't have ICMP access to so validating that the tunnel is up is good enough.
<br/>
	We now have the index we need to check the state of the tunnel:
<pre>
	snmpget -v 1 -Os -c myrocommunity 1.1.1.1 nsVpnMonP2State.0
	nsVpnMonP2State.0 = INTEGER: active(1)
</pre>
	Thus we use check_snmp to see our tunnel state. You might notice that this is a check against the Phase 2 state of the tunnel. This only validates that the Phase 2 is built. Reachability THROUGH the tunnel is not validated. 
<p>	If you don't want to worry about the MIB files then you can use the following OIDs:</p>
<br/>	.1.3.6.1.4.1.3224.4.1.1.1    = nsVpnMon
<br/>	.1.3.6.1.4.1.3224.4.1.1.1.4  = nsVpnMonVpnName
<br/>	.1.3.6.1.4.1.3224.4.1.1.1.23 = nsVpnMonP2State

<hr/>
<h1>Name </h1>
	<h3>check_j2ee_container (uses check_http)</h3>

<h1>Purpose</h1>
	Check availability of a J2EE application inside the container

<h3>checkcommands.cfg stanza:</h3>
<pre>
	define command {
	        command_name    check_j2ee_container
		command_line    $USER1$/check_http -H $HOSTNAME$ -p $ARG1$ -u /Ping.do -s pong -c 5 -w 3
	}
</pre>
<h3>Notes:</h3>
<br/>	Okay this is a cheat of sorts and no REAL testing. I suppose we could go one further and use JMX (depending on the container) but this is an easy way to test beyond checking if port 80 is open or checking if a Tomcat process is running. Since we developed our own app internally, one thing we asked for was a static page served inside the servlet. This allows us to check the full connectivity of the webapp through the load balancers to apache frontends (which normally serve static content) to J2EE container. 
<p>	If you get REALLY crazy, you could have the page be a bit more dynamic and try to grab a database connection from the connection pool or follow through any web services you interact with inside the app. This pushes some work over to your developers but they aren't the ones getting called at 1AM because "MyApp is down".</p>
<p>	We use times of 3 seconds for a warning and 5 for a critical. If the container is taking 3 seconds to server STATIC content, you can guess there is problem somewhere. This has helped us to catch many problems such as a container taking a heapdump or our load balancer overallocating to a single server.</p>
<hr/>
</body>
</html>
