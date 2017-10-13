﻿using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.Video;
using UnityStandardAssets.Characters.FirstPerson;

public class Player_Custom_Script : MonoBehaviour {

	private Canvas canvas_ti;
	private Canvas canvas_v;
	private Canvas canvas_a;
	private Camera camera, camera2;

	void Start () {
		canvas_ti = GameObject.Find ("canvas_ti").GetComponent<Canvas> ();
		canvas_v = GameObject.Find ("canvas_v").GetComponent<Canvas> ();
		canvas_a = GameObject.Find ("canvas_a").GetComponent<Canvas> ();

		camera = GameObject.Find ("FirstPersonCharacter").GetComponent<Camera> ();

		// camera 2 is a camera on a separate place for viewing the selected 3D model
		camera2 = GameObject.Find ("camera2").GetComponent<Camera> ();
	}
	

	void OnTriggerEnter(Collider other)
	{
		if (other.gameObject.tag == "poi_imagetext") {

			// Get the name of the sprite from the collided object
			string spriteName = other.gameObject.GetComponent<DisplayPOI_Script> ().imageSpriteNameToShow;

			// Set sprite and text in panel
			GameObject.Find ("img_ti").GetComponent<Image> ().sprite = Resources.Load<Sprite> (spriteName);
			GameObject.Find ("txt_ti").GetComponent<Text> ().text = other.gameObject.GetComponent<DisplayPOI_Script> ().textToShow;

			canvas_ti.enabled = true;

		} else if (other.gameObject.tag == "poi_video") {

			// Get the name of the sprite from the collided object
			string videoName = other.gameObject.GetComponent<DisplayPOI_Script> ().videoToShow;

			GameObject.Find("panel_v").GetComponent<VideoPlayer>().clip = Resources.Load<VideoClip> (videoName);

			canvas_v.enabled = true;
		} else if (other.gameObject.tag == "poi_artefact") {

			canvas_a.enabled = true;

			// show text on canvas
			GameObject.Find ("txt_a").GetComponent<Text> ().text = other.gameObject.GetComponent<DisplayPOI_Script> ().textToShow;

			// Copy mesh 
			Instantiate(other.gameObject.transform.GetChild(0), GameObject.Find ("meshcontainer").transform);

			camera.enabled = false;
			camera2.enabled = true;

			gameObject.GetComponent<FirstPersonController> ().enabled = false;
		}

	}


	void OnTriggerExit(Collider other)
	{
		if (other.gameObject.tag == "poi_imagetext") {
			canvas_ti.enabled = false;
		} else if (other.gameObject.tag == "poi_video") {
			canvas_v.enabled = false;
		} else if (other.gameObject.tag == "poi_artefact") {
			
			// It is not possible because FPS is disabled. Done with button that calls closeArtefactView

		}
	}


	public void closeArtefactView(){

		canvas_a.enabled = false;

		camera.enabled = true;
		camera2.enabled = false;

		// Destroy the copied object
		Destroy (GameObject.Find ("meshcontainer").transform.GetChild (0).gameObject);

		// Enable FPS
		gameObject.GetComponent<FirstPersonController> ().enabled = true;
	}


	void Update(){
		if (camera2.isActiveAndEnabled) {

			// Rotation
			GameObject.Find ("meshcontainer").transform.Rotate (
				new Vector3 (Input.GetAxis ("Mouse Y") * Time.deltaTime * 200, Input.GetAxis ("Mouse X") * Time.deltaTime * 200, 0)
			);


			// Scaling
			if (Input.GetAxis ("Mouse ScrollWheel") != 0) {
				Vector3 targetScale = GameObject.Find ("meshcontainer").transform.localScale - 
					7 * Input.GetAxis ("Mouse ScrollWheel") * (new Vector3 (1, 1, 1));
				
				if (targetScale.x > 0)
					GameObject.Find ("meshcontainer").transform.localScale = 
						Vector3.Lerp (GameObject.Find ("meshcontainer").transform.localScale, targetScale, 10 * Time.deltaTime);
			}
		
		
		}

	}
}
