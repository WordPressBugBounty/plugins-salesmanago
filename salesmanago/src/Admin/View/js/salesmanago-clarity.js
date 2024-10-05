(function() {
        const script = document.createElement( 'script' );
        script.setAttribute( 'type', 'text/javascript' );
        script.innerHTML =
            '(function(c,l,a,r,i,t,y){\n' +
            'c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};\n' +
            't=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;\n' +
            'y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);\n' +
            '})(window, document, "clarity", "script", "f8z2uh5wzy");';
        document.body.appendChild( script );
})();
